// Progress Bar
document.addEventListener("DOMContentLoaded", function () {
  const progressBar = document.querySelector(".progress-bar");
  window.addEventListener("scroll", function () {
    const winScroll =
      document.body.scrollTop || document.documentElement.scrollTop;
    const height =
      document.documentElement.scrollHeight -
      document.documentElement.clientHeight;
    const scrolled = (winScroll / height) * 100;
    progressBar.style.width = scrolled + "%";
  });

  // Counter Animation
  const counters = document.querySelectorAll(".counter");
  let started = false;

  function startCounter() {
    counters.forEach((counter) => {
      const target = parseInt(counter.getAttribute("data-target"));
      const suffix = counter.getAttribute('data-suffix' || "");
      const increment = target / 100;
      let current = 0;

      const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
          counter.textContent = target + suffix;
          clearInterval(timer);
        } else {
          counter.textContent = Math.floor(current) + suffix;
        }
      }, 20);
    });
  }

  function checkScroll() {
    const statsSection = document.querySelector(".bg-gradient-to-r");
    const rect = statsSection.getBoundingClientRect();
    if (rect.top <= window.innerHeight && rect.bottom >= 0 && !started) {
      started = true;
      startCounter();
    }
  }

  window.addEventListener("scroll", checkScroll);

  // Smooth Scroll for navigation links
  const links = document.querySelectorAll('a[href^="#"]');
  links.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href").substring(1);
      const targetElement = document.getElementById(targetId);
      if (targetElement) {
        const offsetTop = targetElement.offsetTop - 80;
        window.scrollTo({
          top: offsetTop,
          behavior: "smooth",
        });
      }
    });
  });

  function setActiveNavLink() {
    const currentPath = window.location.pathname;

    const pathParts = currentPath.split("/").filter((part) => part !== "");
    const currentPage = pathParts[pathParts.length - 1] || "home";

    const navLinks = document.querySelectorAll(
      "header nav a[href]:not(.bg-primary)"
    );

    navLinks.forEach((link) => {
      const linkHref = link.getAttribute("href");
      if (linkHref === currentPage) {
        link.classList.add("nav-link-active");
        link.classList.remove("text-gray-700");
      } else {
        link.classList.remove("nav-link-active");
        link.classList.add("text-gray-700");
      }
    });
  }

  function mobileToggle() {
    // Mobile menu toggle
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");
    const menuIcon = document.getElementById("menu-icon");

    if (mobileMenuButton && mobileMenu) {
      mobileMenuButton.addEventListener("click", () => {
        const isHidden = mobileMenu.classList.contains("mobile-menu-hidden");
        mobileMenu.classList.toggle("mobile-menu-hidden", !isHidden);

        menuIcon.className = isHidden
          ? "ri-close-line text-xl"
          : "ri-menu-line text-xl";
      });

      // Cerrar al hacer clic en enlace
      const mobileLinks = mobileMenu.querySelectorAll("a");
      mobileLinks.forEach((link) => {
        link.addEventListener("click", () => {
          mobileMenu.classList.add("mobile-menu-hidden");
          menuIcon.className = "ri-menu-line text-xl";
        });
      });

      // Cerrar al hacer clic fuera
      document.addEventListener("click", (e) => {
        if (!e.target.closest("header")) {
          mobileMenu.classList.add("mobile-menu-hidden");
          menuIcon.className = "ri-menu-line text-xl";
        }
      });
    }
  }

  mobileToggle();

  //Execute to load page
  setActiveNavLink();
});
