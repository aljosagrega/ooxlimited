"use client";

import { createContext, useContext, useState, useCallback } from "react";

export interface ConfirmOptions {
  title: string;
  message?: string;
  confirmLabel?: string;
  danger?: boolean;
}

interface ConfirmContextValue {
  confirm: (opts: ConfirmOptions) => Promise<boolean>;
}

const ConfirmContext = createContext<ConfirmContextValue>({ confirm: async () => false });

export function useConfirm() {
  return useContext(ConfirmContext).confirm;
}

interface Pending {
  opts: ConfirmOptions;
  resolve: (v: boolean) => void;
}

export function ConfirmDialogProvider({ children }: { children: React.ReactNode }) {
  const [pending, setPending] = useState<Pending | null>(null);

  const confirm = useCallback((opts: ConfirmOptions): Promise<boolean> => {
    return new Promise((resolve) => setPending({ opts, resolve }));
  }, []);

  function handle(value: boolean) {
    pending?.resolve(value);
    setPending(null);
  }

  return (
    <ConfirmContext.Provider value={{ confirm }}>
      {children}
      {pending && (
        <>
          <style>{`@keyframes cdIn{from{opacity:0;transform:scale(0.95) translateY(6px)}to{opacity:1;transform:scale(1) translateY(0)}}`}</style>
          <div
            onClick={() => handle(false)}
            style={{
              position: "fixed", inset: 0, zIndex: 9998,
              background: "rgba(0,0,0,0.5)",
              backdropFilter: "blur(6px)", WebkitBackdropFilter: "blur(6px)",
              display: "flex", alignItems: "center", justifyContent: "center",
              padding: "0 20px",
            }}
          >
            <div
              onClick={(e) => e.stopPropagation()}
              style={{
                background: "var(--at-card)", border: "1px solid var(--at-border)",
                borderRadius: 20, padding: "28px 28px 24px", width: "100%", maxWidth: 400,
                boxShadow: "0 32px 80px rgba(0,0,0,0.45)",
                animation: "cdIn 0.18s cubic-bezier(0.34,1.4,0.64,1)",
              }}
            >
              <div style={{
                fontSize: 16, fontWeight: 600, color: "var(--at-text)",
                marginBottom: pending.opts.message ? 8 : 24, letterSpacing: "-0.01em",
              }}>
                {pending.opts.title}
              </div>
              {pending.opts.message && (
                <div style={{ fontSize: 14, color: "var(--at-muted)", lineHeight: 1.55, marginBottom: 24 }}>
                  {pending.opts.message}
                </div>
              )}
              <div style={{ display: "flex", gap: 8, justifyContent: "flex-end" }}>
                <button onClick={() => handle(false)} className="btn btn-secondary">Cancel</button>
                <button
                  onClick={() => handle(true)}
                  className={`btn ${pending.opts.danger ? "btn-danger" : "btn-primary"}`}
                >
                  {pending.opts.confirmLabel ?? (pending.opts.danger ? "Delete" : "Confirm")}
                </button>
              </div>
            </div>
          </div>
        </>
      )}
    </ConfirmContext.Provider>
  );
}
