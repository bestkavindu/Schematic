// ===== nav scroll state =====
const nav = document.getElementById("nav");
const onScroll = () => nav.classList.toggle("scrolled", window.scrollY > 8);
onScroll();
window.addEventListener("scroll", onScroll, { passive: true });

// ===== mobile menu =====
const toggle = document.getElementById("navToggle");
const menu = document.getElementById("mobileMenu");
toggle?.addEventListener("click", () => menu.classList.toggle("open"));
menu?.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => menu.classList.remove("open")));

// ===== scroll reveal =====
const io = new IntersectionObserver((entries) => {
  entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } });
}, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
document.querySelectorAll(".reveal").forEach((el) => io.observe(el));

// ===== relationship connectors (curved + crow's-foot) =====
// Draw a one-to-many connector from a source card's right edge to a target row.
function drawConnectors(svgId, diagramId, links) {
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
  });
}

function drawAll() {
  // hero: users.id -> posts.user_id (row1), users.id -> comments (via post)
  drawConnectors("heroSvg", "heroDiagram", [
    { from: "users", to: "posts", fromRow: 0, toRow: 1, color: "#3b82f6" },
    { from: "posts", to: "comments", fromRow: 0, toRow: 1, color: "#10b981" },
  ]);
  drawConnectors("scSvg", "scDiagram", [
    { from: "users", to: "posts", fromRow: 0, toRow: 1, color: "#3b82f6" },
  ]);
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
