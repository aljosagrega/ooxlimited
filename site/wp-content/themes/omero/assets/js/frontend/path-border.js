function generateSvgPath({width, height, path, radius}) {
    const cc = function (x, y) {
        let fx = 0, fy = 0;
        if (x.includes('calc')) {
            let tmp = x.replace('calc(', '').replace(')', '');
            if (tmp.includes('+')) {
                tmp = tmp.split('+');
                fx = (parseFloat(tmp[0]) / 100) * width + parseFloat(tmp[1]);
            } else {
                tmp = tmp.split('-');
                fx = (parseFloat(tmp[0]) / 100) * width - parseFloat(tmp[1]);
            }
        } else if (x.includes('%')) {
            fx = (parseFloat(x) / 100) * width;
        } else if (x.includes('px')) {
            fx = parseFloat(x);
        }

        if (y.includes('calc')) {
            let tmp = y.replace('calc(', '').replace(')', '');
            if (tmp.includes('+')) {
                tmp = tmp.split('+');
                fy = (parseFloat(tmp[0]) / 100) * height + parseFloat(tmp[1]);
            } else {
                tmp = tmp.split('-');
                fy = (parseFloat(tmp[0]) / 100) * height - parseFloat(tmp[1]);
            }
        } else if (y.includes('%')) {
            fy = (parseFloat(y) / 100) * height;
        } else if (y.includes('px')) {
            fy = parseFloat(y);
        }
        return [fx, fy];
    };

    function getT1T2C(P0, P1, P2) {
        const errorTolCenter = 1e-4;

        function findCenter(T, d, r, dirTan) {
            const dn = Math.abs(d.x) < Math.abs(d.y)
                ? {x: 1, y: -d.x / d.y}
                : {x: -d.y / d.x, y: 1};

            if (dn.x * dirTan.x + dn.y * dirTan.y < 0) {
                dn.x = -dn.x;
                dn.y = -dn.y;
            }

            const norm = Math.hypot(dn.x, dn.y);
            return {x: T[0] + (r * dn.x) / norm, y: T[1] + (r * dn.y) / norm};
        }

        const dir1 = {x: P0[0] - P1[0], y: P0[1] - P1[1]};
        const dir2 = {x: P2[0] - P1[0], y: P2[1] - P1[1]};

        if ((dir1.x === 0 && dir1.y === 0) || (dir2.x === 0 && dir2.y === 0)) {
            return {T1: P0, T2: P1, C: P0};
        }

        const dir1Mag = Math.hypot(dir1.x, dir1.y);
        const dir2Mag = Math.hypot(dir2.x, dir2.y);

        const dir1Unit = {x: dir1.x / dir1Mag, y: dir1.y / dir1Mag};
        const dir2Unit = {x: dir2.x / dir2Mag, y: dir2.y / dir2Mag};

        const dp = dir1Unit.x * dir2Unit.x + dir1Unit.y * dir2Unit.y;
        if (Math.abs(dp) > 0.999999) {
            return {T1: undefined, T2: undefined, C: undefined};
        }

        const angle = Math.acos(dp);
        const distToTangent = radius / Math.tan(0.5 * angle);

        const T1 = [P1[0] + distToTangent * dir1Unit.x, P1[1] + distToTangent * dir1Unit.y];
        const T2 = [P1[0] + distToTangent * dir2Unit.x, P1[1] + distToTangent * dir2Unit.y];

        const dirT2_T1 = {x: T2[0] - T1[0], y: T2[1] - T1[1]};
        const dirT1_T2 = {x: -dirT2_T1.x, y: -dirT2_T1.y};

        const C1 = findCenter(T1, dir1Unit, radius, dirT2_T1);
        const C2 = findCenter(T2, dir2Unit, radius, dirT1_T2);

        const deltaC = {x: C2.x - C1.x, y: C2.y - C1.y};
        const error = deltaC.x * deltaC.x + deltaC.y * deltaC.y;
        if (error > errorTolCenter) {
            return {T1: undefined, T2: undefined, C: undefined};
        }

        const C = [C1.x + 0.5 * deltaC.x, C1.y + 0.5 * deltaC.y];
        return {T1, T2, C};
    }

    const points = path.split(",");
    const Ppoints = [];
    const Cpoints = [];
    points.forEach((point, i) => {
        const [x, y, r] = point.trim().split(/(?!\(.*)\s(?![^(]*?\))/g);
        const [px, py] = cc(x, y);
        Ppoints.push([px, py]);
    });

    for (let i = 0; i < Ppoints.length; i++) {
        const nextIndex = (i + 1) % Ppoints.length;
        const sequentialIndex = (i + 2) % Ppoints.length;
        const position = getT1T2C(Ppoints[i], Ppoints[nextIndex], Ppoints[sequentialIndex]);
        Cpoints.push(position);
    }
    let svgPath = `M${Cpoints[0]["T1"][0]},${Cpoints[0]["T1"][1]}`;
    for (let i = 0; i < Cpoints.length; i++) {
        const nextIndex = (i + 1) % Cpoints.length;
        let {C, T1, T2} = Cpoints[i];
        const distance = (x1, y1, x2, y2) => Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
        const rx = distance(C[0], C[1], T1[0], T1[1]);
        const angle = Math.atan2(T1[1] - C[1], T1[0] - C[0]) * (180 / Math.PI);
        const largeArcFlag = Math.abs(angle) > 180 ? 1 : 0;
        const sweepFlag = 1;
        svgPath += ` A${rx} ${rx} 0 ${largeArcFlag} ${sweepFlag} ${T2[0]} ${T2[1]} L${Cpoints[nextIndex]["T1"][0]},${Cpoints[nextIndex]["T1"][1]}`;
    }
    svgPath += `Z`;
    return svgPath;
}

(function ($) {
    function updatePaths() {
        $('.omero-path-wrapper').each(function () {
            const $button = $(this);
            const width = $button.outerWidth();
            const height = $button.outerHeight();
            const path = $button.css('--path');
            const radius = parseFloat($button.css('--path-radius'));

            if (path && radius) {
                const svgPath = generateSvgPath({width, height, path, radius});
                if (svgPath) {
                    $button.css('clip-path', 'path("' + svgPath + '")');
                    if ($button.hasClass('omero-path-border')) {
                        let $svg = $button.find('svg.path-border');
                        if (!$svg.length) {
                            $svg = $('<svg vector-effect="non-scaling-stroke" class="path-border" xmlns="http://www.w3.org/2000/svg" fill="none"></svg>');
                            $button.append($svg);
                        }
                        let id = "id" + Math.random().toString(16).slice(2);
                        $svg.attr('viewBox', `0 0 ${width} ${height}`);
                        let innerSvg = '';
                        let dataPath = '';
                        if ($button.hasClass('path-border-gradient-yes')) {
                            innerSvg += ` <defs><linearGradient id="${id}" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" stop-color="rgba(255,255,255,0.2)" /><stop offset="100%" stop-color="transparent" /></linearGradient></defs>`;
                            dataPath += `stroke="url(#${id})"`;
                        }

                        $svg.html(`${innerSvg}<path d="${svgPath}" ${dataPath} />`);

                    }
                }
            }
        });
    }

    $(window).on('resize', function () {
        updatePaths();
    });
    $(document).ready(function () {
        updatePaths();

        $(window).on('load', function () {
            function checkAllElementsRendered() {
                const allRendered = $('.omero-path-wrapper').toArray().every((el) => {
                    const $el = $(el);
                    return $el.outerWidth() > 10 && $el.outerHeight() > 10;
                });
                if (allRendered) {
                    $(document).trigger('omero-path-reload');
                }
            }
            checkAllElementsRendered();
        });
    });

    $(document).on('omero-path-reload', function () {
        updatePaths();
    });
})(jQuery);