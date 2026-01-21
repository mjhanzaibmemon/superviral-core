const buyPackages = document.querySelectorAll(
  ".hero .newmobilepackages .newpackage, .hero .packages-md .card-package"
);

window.addEventListener("pageshow", (event) => {
  try {
    if (event.persisted) {
      // Reset your element's state here
      buyPackages.forEach((p) => p.classList.remove("loading"));
    }
  } catch (error) {
    console.error(error);
  }
});

try {
  const spinner = document.createElement("div");
  spinner.classList.add("spinner");
  const overlay = document.createElement("div");
  overlay.classList.add("loading-overlay");
  buyPackages.forEach((p) => {
    p.appendChild(spinner.cloneNode(true));
    p.appendChild(overlay.cloneNode(true));
    const cta = p.classList.contains("newpackage")
      ? p
      : p.querySelector(".btn-primary");
    cta.addEventListener("click", () => {
      p.classList.add("loading");
    });
  });
} catch (error) {
  console.error(error);
}

const addSwiperDragging = (element) => {
  try {
    let isDown = false;
    let startX;
    let scrollLeft;

    element.addEventListener("mousedown", (e) => {
      e.preventDefault();
      isDown = true;
      startX = e.pageX - element.offsetLeft;
      scrollLeft = element.scrollLeft;
      element.style.cursor = "grabbing";
    });

    element.addEventListener("mouseleave", (e) => {
      e.preventDefault();
      isDown = false;
      element.classList.remove("dragging");
      element.style.cursor = "grab";
    });

    element.addEventListener("mouseup", (e) => {
      e.preventDefault();
      isDown = false;
      setTimeout(() => {
        element.classList.remove("dragging");
      }, 500);
      element.style.cursor = "grab";
    });

    element.addEventListener("mousemove", (e) => {
      if (!isDown) return;
      e.preventDefault();
      element.classList.add("dragging");
      const x = e.pageX - element.offsetLeft;
      const walk = (x - startX) * 2; //scroll-speed
      element.scrollLeft = scrollLeft - walk;
    });
  } catch (error) {
    console.error(error);
  }
};

const swiperContainers = document.querySelectorAll(".swiper-container");
try {
  swiperContainers.forEach((sc) => addSwiperDragging(sc));
} catch (error) {
  console.error(error);
}

// Carousel indicators functionality
class Carousel {
  constructor(swiperContainer, prevButton, nextButton, packageWidth = 120, packageGap = 20) {
    this.swiperContainer = swiperContainer;
    this.prevButton = prevButton;
    this.nextButton = nextButton;
    this.packageWidth = packageWidth;
    this.packageGap = packageGap;
    this.currentIndex = 0;

    this.initialize();
  }

  initialize() {
    this.setupEventListeners();
    this.handleResize();
  }

  calculateVisiblePackages() {
    const style = window.getComputedStyle(this.swiperContainer);
    const paddingLeft = parseFloat(style.paddingLeft);
    const containerWidth = this.swiperContainer.clientWidth - paddingLeft;
    this.visiblePackages = Math.floor(containerWidth / (this.packageWidth + this.packageGap));
  }

  setupEventListeners() {
    this.nextButton.addEventListener('click', () => this.handleNext());
    this.prevButton.addEventListener('click', () => this.handlePrev());
    this.swiperContainer.addEventListener('scroll', () => this.handleScroll());
    window.addEventListener('resize', () => this.handleResize());
  }

  handleNext() {
    this.moveToIndex(this.currentIndex + this.visiblePackages);
  }

  handlePrev() {
    this.moveToIndex(this.currentIndex - this.visiblePackages);
  }

  handleScroll() {
    this.currentIndex = Math.round(this.swiperContainer.scrollLeft / (this.packageWidth + this.packageGap));
    this.updateButtons();
  }

  handleResize() {
    this.calculateVisiblePackages();
    this.updateButtons();
  }

  moveToIndex(index) {
    const offset = index * (this.packageWidth + this.packageGap);
    this.swiperContainer.style.scrollBehavior = 'smooth';
    this.swiperContainer.scrollLeft = offset;
    
    // Reset smooth scroll after animation
    setTimeout(() => {
      this.swiperContainer.style.scrollBehavior = 'auto';
    }, 300);
  }

  updateButtons() {
    const maxScrollLeft = this.swiperContainer.scrollWidth - this.swiperContainer.clientWidth;
    this.prevButton.disabled = this.swiperContainer.scrollLeft === 0;
    this.nextButton.disabled = this.swiperContainer.scrollLeft >= maxScrollLeft;
  }
}

// Initialize carousels for all swiper containers
document.querySelectorAll('.hero .packages-md').forEach(section => {
  try {
    const swiperContainer = section.querySelector('.swiper-container');
    const prevButton = section.querySelector('.indicator.prev');
    const nextButton = section.querySelector('.indicator.next');

    if (swiperContainer && prevButton && nextButton) {
      // Temporary visibility enforcement for accurate measurements
      const originalDisplay = section.style.display;
      section.style.display = 'block';
      new Carousel(swiperContainer, prevButton, nextButton);
      section.style.display = originalDisplay;
    }
  } catch (error) {
    console.error('Error initializing carousel:', error);
  }
});