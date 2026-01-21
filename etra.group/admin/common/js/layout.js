//----------------------------
// Header
//----------------------------
// For mobile menu toggle
try {
  const mobileMenuToggler = document.querySelector(
    ".header .navigation-mobile .nav-top-row .mobile-menu-toggler"
  );
  const menu = document.querySelector(".header .navigation-mobile .full-menu-mobile");
  if (mobileMenuToggler && menu) mobileMenuToggler.addEventListener("click", () => {
    menu.classList.toggle("active");
  });
} catch (error) {
    console.error(error);
}

// For all dropdowns.
try {
  const dropdowns = document.querySelectorAll(".header .dropdown");

  dropdowns.forEach(function (dropdown) {
    const dButton = dropdown.querySelector(".dropdown-toggle");
    const dMenu = dropdown.querySelector(".dropdown-menu");

    dButton.addEventListener("click", () => {
      dMenu.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
      if (!dButton.contains(event.target) && !dMenu.contains(event.target)) {
        dMenu.classList.remove("show");
      }
    });
  });
} catch (error) {
  console.error(error);
}

// ------------------------------------

const btnLogo = document.querySelector(".header .websites .btn-logo");
btnLogo.addEventListener("click", function (event) {
  event.stopPropagation();
  document.getElementById("popup").style.display = "block";
  document.getElementById("overlay").style.display = "block";
});

function closePopup() {
  document.getElementById("popup").style.display = "none";
  document.getElementById("overlay").style.display = "none";
}
