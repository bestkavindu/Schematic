const $ = (id) => document.getElementById(id);

// ===== nav scroll state =====
const nav = $("nav");
if (nav) {
  const onScroll = () => nav.classList.toggle("scrolled", window.scrollY > 8);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
}

// ===== mobile menu =====
const toggle = $("navToggle"), menu = $("mobileMenu");
toggle?.addEventListener("click", () => menu.classList.toggle("open"));
menu?.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => menu.classList.remove("open")));

// ===== FAQ accordion =====
document.querySelectorAll("#faqList .faq-item").forEach((item) => {
  const q = item.querySelector(".faq-q");
  const a = item.querySelector(".faq-a");
  q.addEventListener("click", () => {
    const open = item.classList.contains("open");
    document.querySelectorAll("#faqList .faq-item").forEach((it) => {
      it.classList.remove("open");
      it.querySelector(".faq-a").style.maxHeight = null;
    });
    if (!open) { item.classList.add("open"); a.style.maxHeight = a.scrollHeight + "px"; }
  });
});

// Form behaviour (topic chips, validation, submit, success) is handled by Livewire — see contact-form.blade.php.
