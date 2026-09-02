/**
 * Minimal PHP `serialize()` reader — enough for WordPress postmeta / options
 * (strings, ints, floats, bools, null, arrays, and objects treated as arrays).
 * Byte-length prefixes on strings are respected so multibyte content survives.
 */
type PhpValue = string | number | boolean | null | PhpValue[] | { [k: string]: PhpValue };

export default function phpUnserialize(input: string): PhpValue {
  const bytes = Buffer.from(input, "utf-8");
  let pos = 0;

  const readUntil = (ch: string): string => {
    const idx = bytes.indexOf(ch.charCodeAt(0), pos);
    const out = bytes.toString("utf-8", pos, idx);
    pos = idx + 1;
    return out;
  };

  const expect = (ch: string) => {
    if (bytes[pos] !== ch.charCodeAt(0)) {
      throw new Error(`php-unserialize: expected '${ch}' at ${pos}, got '${String.fromCharCode(bytes[pos])}'`);
    }
    pos++;
  };

  function parse(): PhpValue {
    const type = String.fromCharCode(bytes[pos]);
    switch (type) {
      case "N":
        pos += 2; // N;
        return null;
      case "b": {
        pos += 2; // b:
        const v = bytes[pos] === "1".charCodeAt(0);
        pos += 2; // digit + ;
        return v;
      }
      case "i": {
        pos += 2; // i:
        return parseInt(readUntil(";"), 10);
      }
      case "d": {
        pos += 2; // d:
        return parseFloat(readUntil(";"));
      }
      case "s": {
        pos += 2; // s:
        const len = parseInt(readUntil(":"), 10);
        expect('"');
        const str = bytes.toString("utf-8", pos, pos + len);
        pos += Buffer.byteLength(str, "utf-8");
        expect('"');
        expect(";");
        return str;
      }
      case "a": {
        pos += 2; // a:
        const count = parseInt(readUntil(":"), 10);
        expect("{");
        const entries: [PhpValue, PhpValue][] = [];
        for (let i = 0; i < count; i++) {
          const key = parse();
          const val = parse();
          entries.push([key, val]);
        }
        expect("}");
        // numeric sequential keys → array, else object
        const isList = entries.every(([k], i) => k === i);
        if (isList) return entries.map(([, v]) => v);
        const obj: { [k: string]: PhpValue } = {};
        for (const [k, v] of entries) obj[String(k)] = v;
        return obj;
      }
      case "O": {
        pos += 2; // O:
        const nameLen = parseInt(readUntil(":"), 10);
        expect('"');
        pos += nameLen;
        expect('"');
        expect(":");
        const count = parseInt(readUntil(":"), 10);
        expect("{");
        const obj: { [k: string]: PhpValue } = {};
        for (let i = 0; i < count; i++) {
          const k = parse();
          obj[String(k)] = parse();
        }
        expect("}");
        return obj;
      }
      default:
        throw new Error(`php-unserialize: unknown type '${type}' at ${pos}`);
    }
  }

  return parse();
}
