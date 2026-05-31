// animasi muncul halus
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll('.card').forEach((el, i) => {
        el.style.opacity = 0;
        el.style.transform = "translateY(20px)";

        setTimeout(() => {
            el.style.transition = "0.4s";
            el.style.opacity = 1;
            el.style.transform = "translateY(0)";
        }, i * 100);
    });
});