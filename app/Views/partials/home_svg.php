<svg id="home-svg" class="hidden md:block absolute inset-x-0 bottom-0 w-full pointer-events-none"
  style="top: -70px;"
  viewBox="0 0 100 106"
  preserveAspectRatio="none"
  xmlns="http://www.w3.org/2000/svg">
  <path
    id="road-body"
    d="M 88 0 C 111 27, 12 35, 12 55 C 12 75, 76 88, -5 102"
    fill="none"
    stroke="rgb(var(--color-action))"
    stroke-opacity="0.2"
    stroke-width="36"
    stroke-linecap="butt"
    vector-effect="non-scaling-stroke" />
  <path
    id="road-center"
    d="M 88 0 C 111 27, 12 35, 12 55 C 12 75, 76 88, -5 102"
    fill="none"
    stroke="rgb(var(--color-action))"
    stroke-opacity="0.5"
    stroke-width="3"
    stroke-dasharray="24 32"
    stroke-linecap="square"
    vector-effect="non-scaling-stroke"
    style="animation: snake 2s linear infinite" />
  <style>
    /* md (768px+) : valeurs originales */
    #home-svg {
      mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 70%, transparent 100%);
      -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 70%, transparent 100%);
    }

    /* lg (1024px+) : fondu bas plus large */
    @media (min-width: 1024px) {
      #home-svg {
        mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 55%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 55%, transparent 100%);
      }
    }

    /* xl (1280px+) : fondu bas très étalé */
    @media (min-width: 1280px) {
      #home-svg {
        mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 40%, transparent 100%);
        -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 20%, black 40%, transparent 100%);
      }
    }

    @keyframes snake {
      to {
        stroke-dashoffset: -56;
      }
    }

    .tsw {
      transform-box: fill-box;
      transform-origin: 50% 100%;
    }

    .ta {
      animation: tSway 3.2s ease-in-out infinite;
    }

    .tb {
      animation: tSway 2.8s ease-in-out infinite;
    }

    .tc {
      animation: tSway 3.6s ease-in-out infinite;
    }

    .td {
      animation: tSway 2.5s ease-in-out infinite;
    }

    .te {
      animation: tSway 3.0s ease-in-out infinite;
    }

    .tf {
      animation: tSway 3.4s ease-in-out infinite;
    }

    @keyframes tSway {

      0%,
      100% {
        transform: rotate(0deg);
      }

      25% {
        transform: rotate(4deg);
      }

      75% {
        transform: rotate(-4deg);
      }
    }

    @keyframes rainbowRoad {
      0% {
        stroke: hsl(0, 100%, 58%);
      }

      14% {
        stroke: hsl(50, 100%, 55%);
      }

      28% {
        stroke: hsl(110, 100%, 45%);
      }

      42% {
        stroke: hsl(175, 100%, 48%);
      }

      57% {
        stroke: hsl(220, 100%, 62%);
      }

      71% {
        stroke: hsl(280, 100%, 62%);
      }

      85% {
        stroke: hsl(330, 100%, 60%);
      }

      100% {
        stroke: hsl(360, 100%, 58%);
      }
    }

    @keyframes rainbowRoadCenter {
      0% {
        stroke: hsl(180, 100%, 82%);
      }

      14% {
        stroke: hsl(230, 100%, 82%);
      }

      28% {
        stroke: hsl(280, 100%, 82%);
      }

      42% {
        stroke: hsl(330, 100%, 82%);
      }

      57% {
        stroke: hsl(20, 100%, 82%);
      }

      71% {
        stroke: hsl(70, 100%, 76%);
      }

      85% {
        stroke: hsl(130, 100%, 72%);
      }

      100% {
        stroke: hsl(180, 100%, 82%);
      }
    }

    @keyframes sparkle {

      0%,
      100% {
        opacity: 0;
        r: 0.3;
      }

      50% {
        opacity: 0.95;
        r: 0.85;
      }
    }

    html.rainbow-road #road-body {
      animation: rainbowRoad 3s linear infinite;
      stroke-opacity: 0.9;
      filter: url(#road-glow);
    }

    html.rainbow-road #road-center {
      stroke-opacity: 0.95;
    }
  </style>

  <defs>
    <filter id="road-glow" x="-40%" y="-40%" width="180%" height="180%">
      <feGaussianBlur in="SourceGraphic" stdDeviation="1.8" result="blur" />
      <feMerge>
        <feMergeNode in="blur" />
        <feMergeNode in="SourceGraphic" />
      </feMerge>
    </filter>
    <symbol id="tree" overflow="visible">
      <rect x="-0.35" y="2" width="0.7" height="1.5" />
      <polygon points="0,-1  -2.5,2  2.5,2" />
      <polygon points="0,-3.5  -1.5,0  1.5,0" />
    </symbol>
  </defs>

  <?php
  $bez = fn($t, $p0, $p1, $p2, $p3) => (1 - $t) ** 3 * $p0 + 3 * (1 - $t) ** 2 * $t * $p1 + 3 * (1 - $t) * $t ** 2 * $p2 + $t ** 3 * $p3;
  $sx1 = [88, 111, 12, 12];
  $sy1 = [0, 27, 35, 55];
  $sx2 = [12, 12, 76, -5];
  $sy2 = [55, 75, 88, 102];
  $cls = ['ta', 'tb', 'tc', 'td', 'te', 'tf'];

  $samples = [];
  for ($s = 0; $s <= 200; $s++) {
    $tv = $s / 200;
    $samples[] = [$bez($tv, $sx1[0], $sx1[1], $sx1[2], $sx1[3]), $bez($tv, $sy1[0], $sy1[1], $sy1[2], $sy1[3])];
    $samples[] = [$bez($tv, $sx2[0], $sx2[1], $sx2[2], $sx2[3]), $bez($tv, $sy2[0], $sy2[1], $sy2[2], $sy2[3])];
  }

  $treeTarget = mt_rand(1, 100);
  $count = 0;
  $attempts = 0;
  while ($count < $treeTarget && $attempts < 2000) {
    $attempts++;
    $t  = mt_rand(3, 97) / 100;
    $bx = ($count % 2 === 0) ? $sx1 : $sx2;
    $by = ($count % 2 === 0) ? $sy1 : $sy2;
    $cx = $bez($t, $bx[0], $bx[1], $bx[2], $bx[3]);
    $cy = $bez($t, $by[0], $by[1], $by[2], $by[3]);
    $x  = round(max(-5, min(105, $cx + mt_rand(-22, 22))), 1);
    $y  = round(max(-5, min(105, $cy + mt_rand(-14, 14))), 1);

    $onPath = false;
    foreach ($samples as [$px, $py]) {
      if (($x - $px) ** 2 + ($y - $py) ** 2 < 36) {
        $onPath = true;
        break;
      }
    }
    if ($onPath) continue;

    $sc = round(mt_rand(6, 13) / 10, 1);
    $op = round(mt_rand(12, 26) / 100, 2);
    $dl = round(- (mt_rand(0, 380) / 100), 2);
    $cl = $cls[$count % 6];
    $count++;
    echo "  <g transform=\"translate($x,$y) scale($sc)\" fill=\"rgb(var(--color-action))\" opacity=\"$op\"><use href=\"#tree\" class=\"tsw $cl\" style=\"animation-delay:{$dl}s\"/></g>\n";
  }
  ?>
</svg>