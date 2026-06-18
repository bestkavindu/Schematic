// ===== nav scroll state =====
const nav = document.getElementById("nav");
const onScroll = () => nav.classList.toggle("scrolled", window.scrollY > 8);
onScroll();
window.addEventListener("scroll", onScroll, { passive: true });

// ===== mobile menu =====
const toggle = document.getElementById("navToggle");
const menu = document.getElementById("mobileMenu");
// Mobile-menu behavior lives in the dedicated a11y IIFE at the end of this file.

// ===== scroll reveal =====
const io = new IntersectionObserver((entries) => {
  entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
document.querySelectorAll(".reveal").forEach((el) => io.observe(el));

const prefersReduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

// ===== relationship connectors (curved + crow's-foot) =====
// Draw a one-to-many connector from a source card's right edge to a target row.
// `animate` (used once for the hero) draws the curve in and fades the feet/bar.
function drawConnectors(svgId, diagramId, links, animate) {
  const svg = document.getElementById(svgId);
  const wrap = document.getElementById(diagramId);
  if (!svg || !wrap) return;
  const base = wrap.getBoundingClientRect();
  svg.setAttribute("width", base.width);
  svg.setAttribute("height", base.height);
  svg.innerHTML = "";
  const NS = "http://www.w3.org/2000/svg";

  links.forEach((lk) => {
    const from = wrap.querySelector(`[data-card="${lk.from}"]`);
    const to = wrap.querySelector(`[data-card="${lk.to}"]`);
    if (!from || !to) return;
    const fr = from.getBoundingClientRect(), tr = to.getBoundingClientRect();
    const fCx = fr.left + fr.width / 2, tCx = tr.left + tr.width / 2;
    const fromRight = fCx < tCx;
    // y at given row index (header 38 + row 28*(i+0.5))
    const rowY = (rect, i) => rect.top - base.top + 38 + 28 * i + 14;
    const sx = (fromRight ? fr.right : fr.left) - base.left;
    const sy = rowY(fr, lk.fromRow ?? 0);
    const tRight = !fromRight;
    const tx = (tRight ? tr.right : tr.left) - base.left;
    const ty = rowY(tr, lk.toRow ?? 0);
    const sDir = fromRight ? 1 : -1, tDir = tRight ? 1 : -1;

    const foot = 13;
    const ax = sx + sDir * foot;
    const bx = tx + tDir * 9;
    const barX = tx + tDir * 6;
    const dx = Math.max(46, Math.abs(bx - ax) * 0.45);
    const c1x = ax + sDir * dx, c2x = bx + tDir * dx;
    const stroke = lk.color || "#3b82f6";

    const path = document.createElementNS(NS, "path");
    path.setAttribute("d", `M ${ax} ${sy} C ${c1x} ${sy}, ${c2x} ${ty}, ${bx} ${ty}`);
    path.setAttribute("fill", "none");
    path.setAttribute("stroke", stroke);
    path.setAttribute("stroke-width", "2");
    svg.appendChild(path);

    // Draw the curve in left-to-right via a stroke-dash sweep.
    if (animate) {
      const len = path.getTotalLength();
      path.style.strokeDasharray = len;
      path.style.strokeDashoffset = len;
      path.getBoundingClientRect(); // flush layout so the transition runs
      path.style.transition = "stroke-dashoffset .9s cubic-bezier(.32,.72,0,1) .55s";
      path.style.strokeDashoffset = "0";
    }

    // crow's-foot (many) at source
    const foot1 = document.createElementNS(NS, "path");
    foot1.setAttribute("d", `M ${ax} ${sy} L ${sx} ${sy - 6} M ${ax} ${sy} L ${sx} ${sy} M ${ax} ${sy} L ${sx} ${sy + 6}`);
    foot1.setAttribute("fill", "none");
    foot1.setAttribute("stroke", stroke);
    foot1.setAttribute("stroke-width", "2");
    foot1.setAttribute("stroke-linecap", "round");
    svg.appendChild(foot1);

    // one-bar at target
    const bar = document.createElementNS(NS, "line");
    bar.setAttribute("x1", barX); bar.setAttribute("y1", ty - 6);
    bar.setAttribute("x2", barX); bar.setAttribute("y2", ty + 6);
    bar.setAttribute("stroke", stroke); bar.setAttribute("stroke-width", "2");
    bar.setAttribute("stroke-linecap", "round");
    svg.appendChild(bar);
    const tail = document.createElementNS(NS, "line");
    tail.setAttribute("x1", tx); tail.setAttribute("y1", ty);
    tail.setAttribute("x2", bx); tail.setAttribute("y2", ty);
    tail.setAttribute("stroke", stroke); tail.setAttribute("stroke-width", "2");
    svg.appendChild(tail);

    const dot = document.createElementNS(NS, "circle");
    dot.setAttribute("cx", barX); dot.setAttribute("cy", ty); dot.setAttribute("r", "2.6");
    dot.setAttribute("fill", stroke);
    svg.appendChild(dot);

    // Fade the endpoint markers in once the curve has finished drawing.
    if (animate) {
      [foot1, bar, tail, dot].forEach((el) => {
        el.style.opacity = "0";
        el.style.transition = "opacity .35s ease 1.35s";
      });
      svg.getBoundingClientRect();
      [foot1, bar, tail, dot].forEach((el) => { el.style.opacity = "1"; });
    }
  });
}

let heroDrawn = false;
function drawAll() {
  // Animate the hero connectors once, after a full load (so card geometry is final).
  const animateHero = !heroDrawn && !prefersReduced && document.readyState === "complete";
  // hero: users.id -> posts.user_id (row1), users.id -> comments (via post)
  drawConnectors("heroSvg", "heroDiagram", [
    { from: "users", to: "posts", fromRow: 0, toRow: 1, color: "#3b82f6" },
    { from: "posts", to: "comments", fromRow: 0, toRow: 1, color: "#10b981" },
  ], animateHero);
  if (animateHero) heroDrawn = true;
  drawConnectors("scSvg", "scDiagram", [
    { from: "users", to: "posts", fromRow: 0, toRow: 1, color: "#3b82f6" },
  ], false);
}
drawAll();
window.addEventListener("resize", drawAll);
window.addEventListener("load", drawAll);
if (document.fonts) document.fonts.ready.then(drawAll);
setTimeout(drawAll, 300);

// ===== type chip rotation =====
const chips = [...document.querySelectorAll("#typeChips .type-chip")];
if (chips.length) {
  let ci = chips.findIndex((c) => c.classList.contains("on"));
  setInterval(() => {
    chips[ci]?.classList.remove("on");
    ci = (ci + 1) % chips.length;
    chips[ci]?.classList.add("on");
  }, 1400);
}

// ===== code tabs =====
const CODE = {
  laravel: [
    ['com', '// database/migrations/'],
    ['pun', 'Schema::', 'fnname', 'create', 'pun', '(', 'str', "'posts'", 'pun', ', ', 'k', 'function', 'pun', ' (', 'fnname', 'Blueprint', 'pun', ' $table) {'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'id', 'pun', '();'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'foreignId', 'pun', '(', 'str', "'user_id'", 'pun', ')'],
    ['raw', '          '], ['pun', '->', 'fnname', 'constrained', 'pun', '()->', 'fnname', 'cascadeOnDelete', 'pun', '();'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'string', 'pun', '(', 'str', "'title'", 'pun', ');'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'text', 'pun', '(', 'str', "'body'", 'pun', ');'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'timestamp', 'pun', '(', 'str', "'published_at'", 'pun', ')->', 'fnname', 'nullable', 'pun', '();'],
    ['raw', '    '], ['pun', '$table->', 'fnname', 'timestamps', 'pun', '();'],
    ['pun', '});'],
  ],
  sql: [
    ['k', 'CREATE TABLE ', 'pun', '`posts` ('],
    ['raw', '  '], ['pun', '`id` ', 'k', 'BIGINT UNSIGNED ', 'k', 'NOT NULL ', 'k', 'AUTO_INCREMENT', 'pun', ','],
    ['raw', '  '], ['pun', '`user_id` ', 'k', 'BIGINT UNSIGNED ', 'k', 'NOT NULL', 'pun', ','],
    ['raw', '  '], ['pun', '`title` ', 'k', 'VARCHAR', 'pun', '(', 'num', '255', 'pun', ') ', 'k', 'NOT NULL', 'pun', ','],
    ['raw', '  '], ['pun', '`body` ', 'k', 'TEXT ', 'k', 'NOT NULL', 'pun', ','],
    ['raw', '  '], ['pun', '`published_at` ', 'k', 'TIMESTAMP ', 'k', 'NULL', 'pun', ','],
    ['raw', '  '], ['k', 'PRIMARY KEY ', 'pun', '(`id`),'],
    ['raw', '  '], ['k', 'FOREIGN KEY ', 'pun', '(`user_id`) ', 'k', 'REFERENCES ', 'pun', '`users`(`id`)'],
    ['pun', ');'],
  ],
};
const codeBody = document.getElementById("codeBody");
const codeFn = document.getElementById("codeFn");
function renderCode(tab) {
  const lines = CODE[tab];
  codeBody.innerHTML = lines.map((toks) => {
    let html = "";
    for (let i = 0; i < toks.length; i += 2) {
      const cls = toks[i], txt = toks[i + 1] ?? "";
      const esc = txt.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
      html += cls === "raw" ? esc : `<span class="${cls}">${esc}</span>`;
    }
    return `<span class="ln">${html || " "}</span>`;
  }).join("");
  codeFn.textContent = tab === "laravel" ? "2026_06_13_create_posts_table.php" : "schema.sql";
}
renderCode("laravel");
document.querySelectorAll(".code-tab").forEach((btn) => {
  btn.addEventListener("click", () => {
    document.querySelectorAll(".code-tab").forEach((b) => b.classList.remove("on"));
    btn.classList.add("on");
    renderCode(btn.dataset.tab);
  });
});

// ===== stat count-up =====
// Animate each .stat-n from 0 to its value when it scrolls into view.
// Preserves any prefix/suffix ("<5 min", "12k+") and the original decimal places.
const statEls = [...document.querySelectorAll(".stat-n")];
if (statEls.length && !prefersReduced && "IntersectionObserver" in window) {
  const sio = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      const el = e.target;
      sio.unobserve(el);
      const m = el.textContent.trim().match(/^(\D*)(\d+(?:\.\d+)?)(.*)$/);
      if (!m) return;
      const pre = m[1], target = parseFloat(m[2]), suf = m[3];
      const dec = (m[2].split(".")[1] || "").length;
      const dur = 1100, t0 = performance.now();
      el.textContent = pre + (0).toFixed(dec) + suf;
      const tick = (now) => {
        const p = Math.min(1, (now - t0) / dur);
        const eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
        el.textContent = pre + (target * eased).toFixed(dec) + suf;
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = pre + target.toFixed(dec) + suf;
      };
      requestAnimationFrame(tick);
    });
  }, { threshold: 0.5 });
  statEls.forEach((el) => sio.observe(el));
}

// ===== nav: scroll-spy sliding pill (rAF-throttled, nearest-above-line) =====
(() => {
  const navLinksWrap = document.getElementById("navLinks");
  const pill = document.getElementById("navPill");
  if (!navLinksWrap) return;

  // Only spy links whose target section actually exists on the page, so a
  // missing/commented-out section degrades silently.
  const spyLinks = [...navLinksWrap.querySelectorAll("[data-spy]")]
    .map((a) => ({ a, sec: document.getElementById(a.dataset.spy) }))
    .filter((x) => x.sec);
  if (!spyLinks.length) return;

  let activeLink = null;

  const movePill = (link) => {
    if (!pill) return;
    if (!link) { pill.classList.remove("show"); return; }
    pill.style.width = link.offsetWidth + "px";
    pill.style.transform = `translate(${link.offsetLeft}px, -50%)`;
    pill.classList.add("show");
  };

  const setActive = (link) => {
    if (link === activeLink) return;
    spyLinks.forEach(({ a }) => a.removeAttribute("aria-current"));
    document.querySelectorAll('#mobileMenu a[aria-current]')
      .forEach((a) => a.removeAttribute("aria-current"));
    activeLink = link;
    if (link) {
      link.setAttribute("aria-current", "true");
      // mirror onto the matching mobile-menu link
      const mob = document.querySelector(`#mobileMenu a[href="#${link.dataset.spy}"]`);
      if (mob) mob.setAttribute("aria-current", "true");
    }
    movePill(link);
  };

  // Nearest section whose top is at or above the nav line wins — no strict
  // straddle, so the pill never drops into gaps between sections.
  const LINE = 96; // px below viewport top, clears the 64px bar
  const compute = () => {
    let current = null, bestTop = -Infinity;
    for (const { a, sec } of spyLinks) {
      const top = sec.getBoundingClientRect().top;
      if (top - LINE <= 0 && top > bestTop) { bestTop = top; current = a; }
    }
    // Above the first section (hero) -> no active pill.
    if (window.scrollY < 40) current = null;
    setActive(current);
  };

  // rAF throttle so each scroll event does at most one batched read.
  let ticking = false;
  const spy = () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => { compute(); ticking = false; });
  };

  window.addEventListener("scroll", spy, { passive: true });
  window.addEventListener("resize", () => { if (activeLink) movePill(activeLink); spy(); });
  // Re-measure once layout/fonts settle so pill geometry is exact.
  window.addEventListener("load", spy);
  if (document.fonts) document.fonts.ready.then(() => { if (activeLink) movePill(activeLink); });
  compute();
})();

// ===== nav: mobile menu — full a11y ownership =====
(() => {
  // `toggle` and `menu` already exist from the top-of-file mobile-menu block.
  if (!toggle || !menu) return;

  const setOpen = (open) => {
    menu.classList.toggle("open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
    document.body.style.overflow = open ? "hidden" : "";
  };

  toggle.addEventListener("click", (e) => {
    e.stopPropagation();
    setOpen(!menu.classList.contains("open"));
  });
  menu.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => setOpen(false)));

  // Esc closes and returns focus to the toggle.
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && menu.classList.contains("open")) { setOpen(false); toggle.focus(); }
  });

  // Outside-click closes.
  document.addEventListener("click", (e) => {
    if (!menu.classList.contains("open")) return;
    if (!menu.contains(e.target) && !toggle.contains(e.target)) setOpen(false);
  });

  // Resized up to desktop while open -> reset.
  window.addEventListener("resize", () => {
    if (window.innerWidth > 680 && menu.classList.contains("open")) setOpen(false);
  });
})();
