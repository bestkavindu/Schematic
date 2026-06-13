// ============ Schematic — auth (server-rendered sign in / sign up) ============
// Adapted from the design prototype: the original toggled sign-in/sign-up in one
// page and faked submit. Here each page is its own route and the <form> posts to
// Fortify, so this only handles the progressive-enhancement bits.

const EYE_OPEN = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
const EYE_OFF = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7c2 0 3.7.6 5.2 1.4M22 12s-3.5 7-10 7c-2 0-3.7-.6-5.2-1.4"/><path d="m4 4 16 16"/><path d="M9.5 9.6a3 3 0 0 0 4.2 4.2"/></svg>';

// ===== password show / hide (per input) =====
document.querySelectorAll(".eye").forEach((btn) => {
  btn.addEventListener("click", () => {
    const input = btn.closest(".input-wrap")?.querySelector(".input");
    if (!input) return;
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    btn.innerHTML = show ? EYE_OFF : EYE_OPEN;
    btn.setAttribute("aria-label", show ? "Hide password" : "Show password");
  });
});

// ===== password strength (sign-up only) =====
const pw = document.getElementById("password");
const bars = [...document.querySelectorAll(".strength-bar")];
const strengthLabel = document.getElementById("strengthLabel");
const STR = [
  { label: "Use 8+ characters with a mix of letters & numbers", color: "" },
  { label: "Weak password", color: "#ef4444" },
  { label: "Fair — add numbers or symbols", color: "#f59e0b" },
  { label: "Good password", color: "#3b82f6" },
  { label: "Strong password", color: "#10b981" },
];
function scorePass(v) {
  if (!v) return 0;
  let s = 0;
  if (v.length >= 8) s++;
  if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
  if (/\d/.test(v)) s++;
  if (/[^A-Za-z0-9]/.test(v)) s++;
  return Math.max(1, Math.min(4, s));
}
if (pw && bars.length && strengthLabel) {
  pw.addEventListener("input", () => {
    const s = scorePass(pw.value);
    bars.forEach((b, i) => { b.style.background = i < s ? STR[s].color : "var(--border-strong)"; });
    strengthLabel.textContent = STR[s].label;
    strengthLabel.style.color = s ? STR[s].color : "var(--muted)";
  });
}

// ===== clear the server-error visual state once the user edits a field =====
document.querySelectorAll(".field .input").forEach((input) => {
  input.addEventListener("input", () => {
    input.classList.remove("invalid");
    input.closest(".field")?.classList.remove("error");
  });
});

// ===== loading state on submit (form still posts natively to Fortify) =====
document.querySelectorAll("form").forEach((form) => {
  form.addEventListener("submit", () => {
    const btn = form.querySelector(".btn-submit");
    if (btn) { btn.classList.add("loading"); btn.disabled = true; }
  });
});

// ===== aside connector (users.id -> posts.user_id) =====
function drawAside() {
  const svg = document.getElementById("asideSvg");
  const stage = document.getElementById("asideStage");
  if (!svg || !stage) return;
  const base = stage.getBoundingClientRect();
  svg.setAttribute("width", base.width);
  svg.setAttribute("height", base.height);
  svg.innerHTML = "";
  const NS = "http://www.w3.org/2000/svg";
  const from = stage.querySelector('[data-card="users"]');
  const to = stage.querySelector('[data-card="posts"]');
  if (!from || !to) return;
  const fr = from.getBoundingClientRect(), tr = to.getBoundingClientRect();
  const rowY = (r, i) => r.top - base.top + 36 + 27 * i + 13.5;
  const sx = fr.right - base.left, sy = rowY(fr, 0);     // users.id (row 0)
  const tx = tr.left - base.left, ty = rowY(tr, 1);      // posts.user_id (row 1)
  const foot = 13, ax = sx + foot, bx = tx - 9, barX = tx - 6;
  const dx = Math.max(40, Math.abs(bx - ax) * 0.5);
  const stroke = "rgba(199,210,254,.9)";
  const mk = (tag, attrs) => { const e = document.createElementNS(NS, tag); for (const k in attrs) e.setAttribute(k, attrs[k]); svg.appendChild(e); return e; };
  mk("path", { d: `M ${ax} ${sy} C ${ax + dx} ${sy}, ${bx - dx} ${ty}, ${bx} ${ty}`, fill: "none", stroke, "stroke-width": 2 });
  mk("path", { d: `M ${ax} ${sy} L ${sx} ${sy - 6} M ${ax} ${sy} L ${sx} ${sy} M ${ax} ${sy} L ${sx} ${sy + 6}`, fill: "none", stroke, "stroke-width": 2, "stroke-linecap": "round" });
  mk("line", { x1: barX, y1: ty - 6, x2: barX, y2: ty + 6, stroke, "stroke-width": 2, "stroke-linecap": "round" });
  mk("line", { x1: tx, y1: ty, x2: bx, y2: ty, stroke, "stroke-width": 2 });
  mk("circle", { cx: barX, cy: ty, r: 2.6, fill: stroke });
}
drawAside();
window.addEventListener("resize", drawAside);
window.addEventListener("load", drawAside);
if (document.fonts) document.fonts.ready.then(drawAside);
setTimeout(drawAside, 250);
