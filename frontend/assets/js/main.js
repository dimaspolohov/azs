(function initShowInUp() {
  const selector = ".showInUp";
  const visibleClass = "showInUp--visible";

  const style = document.createElement("style");
  style.textContent = `
    .showInUp {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.6s ease, transform 0.6s ease;
      will-change: opacity, transform;
    }

    .showInUp--visible {
      opacity: 1;
      transform: translateY(0);
    }
  `;
  document.head.appendChild(style);

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add(visibleClass);
        } else {
          entry.target.classList.remove(visibleClass);
        }
      });
    },
    {
      rootMargin: "0px",
      threshold: 0.1,
    }
  );

  const elements = document.querySelectorAll(selector);
  elements.forEach((el) => observer.observe(el));
})();

(function () {
  const btnUp = document.querySelector('.btn_up');
  if (!btnUp) return;

  btnUp.addEventListener('click', function () {
    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });
  });
})();

if(document.querySelector('.header')){

    let header_top_height = document.querySelector('.header').getBoundingClientRect().height
    document.documentElement.style.setProperty('--headerHeight', `${header_top_height}px`)

}

Inputmask({
  mask: "+7 999 999 99 99",
  inputmode: 'numeric',
  showMaskOnFocus: true,
  "clearIncomplete": true,
  clearMaskOnLostFocus: true,
  greedy: false,
  nullable: true,
}).mask("input[type='tel']");


PopupManager.register('popup_cookies', {
    is_block_scroll: false,
    close_controls: false,
  },
);
PopupManager.register('popup_menu', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_appended', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_form', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_callback', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_news', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_success', {
    is_block_scroll: true,
    close_controls: true,
  },
);
PopupManager.register('popup_error', {
    is_block_scroll: true,
    close_controls: true,
  },
);

document.addEventListener('click', function(e) {
  const button = e.target.closest('[data-openpopup]');
  if (!button) return;

  e.preventDefault();
  const popupName = button.dataset.openpopup;

  if (popupName === 'popup_for_gallery') {
    const mediaData = {
      type: button.dataset.mediaType || null,
      src: button.dataset.mediaSrc || null,
      poster: button.dataset.mediaPoster || null
    };
    PopupManager.open(popupName, mediaData);
  } else {
    PopupManager.open(popupName);
  }
});


window.addEventListener('load', () => {
  if (sessionStorage.getItem('cookie') !== 'shown' && localStorage.getItem('cookie') !== 'shown') {
    PopupManager.open('popup_cookies');
    document.querySelectorAll('[data-accept]').forEach((el) => {
      el.addEventListener('click', function() {
        sessionStorage.setItem('cookie', 'shown');
        localStorage.setItem('cookie', 'shown');
      });
    })
  }
});

let banner_slider = document.querySelectorAll('.banner-group');

banner_slider.forEach(banner_sl => {

  let wrapper = banner_sl.querySelector('.block--wrapper');
  let slider_controls = document.createElement('div');
  slider_controls.classList.add('slider_controls');
  wrapper.append(slider_controls);

  let swiper_nav_prev = document.createElement('div');
  swiper_nav_prev.classList.add('swiper-button-prev');
  slider_controls.append(swiper_nav_prev);

  let swiper_pagination = document.createElement('div');
  swiper_pagination.classList.add('swiper_pagination');
  wrapper.append(swiper_pagination);

  let swiper_nav_next = document.createElement('div');
  swiper_nav_next.classList.add('swiper-button-next');
  slider_controls.append(swiper_nav_next);


  const hero_slider = new Swiper(wrapper, {
    createElements: true,
    slideClass: 'banner',
    slidesPerView: 1,
    grabCursor: false,
    // simulateTouch: true,
    allowTouchMove: true,
    autoHeight: true,
    centeredSlides: true,
    focusableElements: 'input, select, option, textarea, button, label, a',
    effect: 'fade',
    loop: true,
    mousewheel: {
      forceToAxis: true,
    },
    navigation: {
      nextEl: swiper_nav_next,
      prevEl: swiper_nav_prev,
    },
    pagination: {
      // type: 'fraction',
      el: swiper_pagination,
    },
  });
});

let about = document.querySelectorAll('.about');
about.forEach(block => {
  if (window.matchMedia('(max-width: 640px)').matches) {

    let wrapper = block.querySelector('.block--list');
    let slider_controls = document.createElement('div');
    slider_controls.classList.add('slider_controls');
    wrapper.append(slider_controls);

    let swiper_pagination = document.createElement('div');
    swiper_pagination.classList.add('swiper_pagination');
    slider_controls.append(swiper_pagination);

    let swiper_nav_prev = document.createElement('div');
    swiper_nav_prev.classList.add('swiper-button-prev');
    slider_controls.append(swiper_nav_prev);

    let swiper_nav_next = document.createElement('div');
    swiper_nav_next.classList.add('swiper-button-next');
    slider_controls.append(swiper_nav_next);


    const hero_slider = new Swiper(wrapper, {
      createElements: true,
      slideClass: 'card',
      slidesPerView: 1,
      grabCursor: false,
      // simulateTouch: true,
      allowTouchMove: true,
      autoHeight: true,
      centeredSlides: true,
      focusableElements: 'input, select, option, textarea, button, label, a',
      effect: 'fade',
      loop: true,
      mousewheel: {
        forceToAxis: true,
      },
      navigation: {
        nextEl: swiper_nav_next,
        prevEl: swiper_nav_prev,
      },
      pagination: {
        // type: 'fraction',
        el: swiper_pagination,
      },
    });
  }
});

let reviews = document.querySelectorAll('.reviews');
reviews.forEach(block => {
  let is_mobile = false;
  if (window.matchMedia('(max-width: 640px)').matches) {
    is_mobile = true;
  }
  let wrapper = block.querySelector('.block--elements');
  let list = block.querySelector('.block--list');
  let slider_controls = document.createElement('div');
  slider_controls.classList.add('slider_controls');
  list.append(slider_controls);

  let swiper_nav_prev = document.createElement('div');
  swiper_nav_prev.classList.add('swiper-button-prev');
  slider_controls.append(swiper_nav_prev);

  let swiper_pagination = document.createElement('div');
  swiper_pagination.classList.add('swiper_pagination');
  slider_controls.append(swiper_pagination);

  let swiper_nav_next = document.createElement('div');
  swiper_nav_next.classList.add('swiper-button-next');
  slider_controls.append(swiper_nav_next);


  const hero_slider = new Swiper(wrapper, {
    createElements: true,
    slideClass: 'card',
    slidesPerView: is_mobile ? 1 : 3,
    grabCursor: false,
    allowTouchMove: true,
    autoHeight: true,
    observer: true,
    observeParents: true,
    focusableElements: 'input, select, option, textarea, button, label, a',
    spaceBetween: parseInt(getComputedStyle(document.documentElement).getPropertyValue('--s-8').trim()) || 8,
    mousewheel: {
      forceToAxis: true,
    },
    navigation: {
      nextEl: swiper_nav_next,
      prevEl: swiper_nav_prev,
    },
    pagination: {
      el: swiper_pagination,
    },
    on: {
      slideChangeTransitionEnd: function() {
        this.updateAutoHeight(200);
      },
    },
  });
});

let cards = document.querySelectorAll('.card');
cards.forEach(block => {

  if (block.querySelectorAll('.card--image .img-slide').length <= 1) {
    return;
  }
  let wrapper = block.querySelector('.card--image');
  let slider_controls = document.createElement('div');
  slider_controls.classList.add('slider_controls');
  wrapper.append(slider_controls);

  let swiper_nav_prev = document.createElement('div');
  swiper_nav_prev.classList.add('swiper-button-prev');
  slider_controls.append(swiper_nav_prev);

  let swiper_pagination = document.createElement('div');
  swiper_pagination.classList.add('swiper_pagination');
  wrapper.append(swiper_pagination);

  let swiper_nav_next = document.createElement('div');
  swiper_nav_next.classList.add('swiper-button-next');
  slider_controls.append(swiper_nav_next);


  const cards_slider = new Swiper(wrapper, {
    createElements: true,
    slideClass: 'img-slide',
    slidesPerView: 1,
    grabCursor: false,
    // simulateTouch: true,
    allowTouchMove: true,
    autoHeight: true,
    centeredSlides: true,
    focusableElements: 'input, select, option, textarea, button, label, a',
    effect: 'fade',
    loop: true,
    mousewheel: {
      forceToAxis: true,
    },
    navigation: {
      nextEl: swiper_nav_next,
      prevEl: swiper_nav_prev,
    },
    pagination: {
      el: swiper_pagination,
    },
  });
});


function ClickOnCardCatalogHero(block){
  if (window.matchMedia('(max-width: 640px)').matches) {
    let cards = block.querySelectorAll('.card');
    cards.forEach(card => {
      // Prevent parent-category navigation on the first tap so the
      // subcategory list can be revealed first; second tap navigates.
      let link = card.querySelector('.card--link');
      if (link) {
        link.addEventListener('click', (e) => {
          if (!card.classList.contains('is_open')) {
            e.preventDefault();
          }
        });
      }
      card.addEventListener('click', (e) => {
        cards.forEach((it) => {
          it !== card ? it.classList.remove("is_open") : it.classList.toggle("is_open");
        });
      })
    })
  }
}
let catalog_hero = document.querySelector('.catalog-hero')
if (catalog_hero){
  ClickOnCardCatalogHero(catalog_hero)
}

function initCategoryDropdowns(container) {
  let dropdowns = container.querySelectorAll(" .item-dropdown");
  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener("click", (e) => {
      e.preventDefault();
      dropdown.classList.toggle("is_open");
    });
  });
}
let category = document.querySelector('.category')
if(category){
  initCategoryDropdowns(category);
}



// marquee
function runMarquee() {
  const allMarquees = document.querySelectorAll('.marquee');
  allMarquees.forEach((marquee, index) => {
    marquee.querySelector('.marquee__wrapper');
    const allItems = marquee.querySelectorAll('.marquee .marquee__item'),
      proxy = document.createElement('div');
    allItems.length;
    let totalX = 0,
      marqueeH = 0;
    marquee.offsetWidth;

    allItems.forEach((item, i) => {
      const itemW = item.offsetWidth,
        itemH = item.offsetHeight;
      (totalX += itemW),
        gsap.set(item, {
          x: totalX,
          width: itemW,
        }),
      itemH > marqueeH && (marqueeH = itemH);
    });
    const marqueeVal = gsap.utils.wrap(0, totalX),
      marqueeProgress = gsap.utils.wrap(0, 1);
    const stringX = `-=${totalX}`,
      animation = gsap.to(allItems, {
        repeat: -1,
        duration: 180,
        x: stringX,
        ease: 'none',
        paused: !0,
        modifiers: {
          x: function (x, target) {
            return `${(x =
              ((parseInt(x) - totalX) % totalX) +
              (totalX - target.offsetWidth))}px`;
          },
        },
      });

    function updateProgress() {
      const dragValue = marqueeVal((this.deltaX / 2) * -1) / totalX,
        currentProgressAnim = animation.progress(),
        endProgress = marqueeProgress(currentProgressAnim + dragValue);
      animation.progress(endProgress);
    }
    Draggable.create(proxy, {
      type: 'x',
      trigger: marquee,
      inertia: !0,
      onDrag: updateProgress,
      onThrowUpdate: updateProgress,
    });
    window.addEventListener('resize', function resize() {
      animation.render(animation.time(), !1, !0);
    });
    animation.play();
  });
}
const marquees = document.querySelectorAll('.marquee');
if (marquees.length) {
  setTimeout(runMarquee, 2000);
}



function createBottomAnimation(selector, triggerSelector) {
  gsap.to(selector, {
    yPercent: -30,
    scale: 1.6,
    ease: "none",
    scrollTrigger: {
      trigger: selector,
      start: "100% 90%",
      end: `+=${window.innerHeight}`,
      scrub: true,
      // markers: true
    }
  });
}
if (document.querySelector('.hero-main__marquee')){
  createBottomAnimation('.hero-main__marquee', '.hero');
}
if (document.querySelector('.about__marquee')){
  createBottomAnimation('.about__marquee', '.about');
}


if (window.matchMedia("(max-width: 640px)").matches) {
  document.addEventListener('touchstart', () => {
    document.querySelectorAll('video').forEach(video => video.play())
  })
}

/**
 * Бегущая строка по структуре из marquee (другой проект):
 * .marquee > .marquee__wrapper > .marquee__item — позиции через modifier, без Draggable.
 */
function runMarquee() {
  const allMarquees = document.querySelectorAll('.suppliers .marquee');
  if (!allMarquees.length || typeof gsap === 'undefined') return;

  allMarquees.forEach((marquee) => {
    const wrapper = marquee.querySelector('.marquee__wrapper');
    if (!wrapper) return;
    const originalItems = Array.from(wrapper.querySelectorAll('.marquee__item'));
    if (!originalItems.length) return;

    const gap = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--s-8').trim(), 10) || 8;
    const marqueeW = marquee.offsetWidth;

    let basePass = 0;
    originalItems.forEach((item) => {
      basePass += item.offsetWidth + gap;
    });

    // Clone items so the strip is at least ~2x the viewport, keeping the
    // loop seamless and every logo sliding in right from the edge.
    if (basePass > 0) {
      const minWidth = Math.max(marqueeW * 2, basePass);
      const copies = Math.ceil(minWidth / basePass);
      for (let i = 1; i < copies; i++) {
        originalItems.forEach((item) => {
          wrapper.appendChild(item.cloneNode(true));
        });
      }
    }

    const allItems = wrapper.querySelectorAll('.marquee__item');
    let totalX = 0;
    let marqueeH = 0;

    allItems.forEach((item) => {
      const itemW = item.offsetWidth;
      const itemH = item.offsetHeight;
      totalX += itemW + gap;
      gsap.set(item, {
        x: totalX,
        width: itemW,
      });
      if (itemH > marqueeH) marqueeH = itemH;
    });

    const stringX = `-=${totalX}`;

    const animation = gsap.to(allItems, {
      repeat: -1,
      duration: 60,
      x: stringX,
      ease: 'none',
      modifiers: {
        x: function (x, target) {
          const parsed = parseFloat(x);
          const wrapped = ((parsed - totalX) % totalX) + (totalX - target.offsetWidth);
          return `${wrapped}px`;
        },
      },
    });

    window.addEventListener('resize', function resize() {
      animation.render(animation.time(), false, true);
    });

    animation.play();
  });
}

function initSuppliersMarquee() {
  const marquees = document.querySelectorAll('.suppliers .marquee');
  if (!marquees.length) return;

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runMarquee);
  } else {
    runMarquee();
  }
}

initSuppliersMarquee();

let initAccordionItems = (block) => {
  let accordion_items = block.querySelectorAll(".accordion");
  accordion_items.forEach((item) => {
    let accordion_button = item.querySelector(".accordion--button");
    accordion_button.addEventListener("click", (e) => {
      accordion_items.forEach((it) => {
        it !== item ? it.classList.remove("is_open") : it.classList.toggle("is_open");
      });
    });
  });
};
initAccordionItems(document);

/**
 * Аккордеон фильтра: открытие/закрытие категорий и вложенных групп.
 */
function initFilterAccordion() {
  const filter = document.querySelector('.filter');
  if (!filter) return;

  const items = filter.querySelectorAll('.filter__item');
  const triggers = filter.querySelectorAll('.filter__button_trigger');
  const groups = filter.querySelectorAll('.filter__group');

  triggers.forEach((btn) => {
    btn.addEventListener('click', function () {
      const item = this.closest('.filter__item');
      const isOpen = item.classList.contains('is-open');
      items.forEach((el) => el.classList.remove('is-open'));
      if (!isOpen) {
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        const content = item.querySelector('.filter__content');
        if (content) content.setAttribute('aria-hidden', 'false');
      } else {
        btn.setAttribute('aria-expanded', 'false');
        const content = item.querySelector('.filter__content');
        if (content) content.setAttribute('aria-hidden', 'true');
      }
    });
  });

  groups.forEach((group) => {
    const title = group.querySelector('.filter__group_title');
    if (!title) return;
    title.addEventListener('click', function () {
      group.classList.toggle('is-open');
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initFilterAccordion);
} else {
  initFilterAccordion();
}

let filterBtns = document.querySelectorAll('.btn-filter')
filterBtns.forEach(btn => {
  btn.addEventListener('click', function () {
    btn.parentElement.querySelector('.filter').classList.toggle('is-open');
  })
})

document.addEventListener('click', function (e) {
  if (e.target.closest('.btn-filter') || e.target.closest('.filter')) return
  document.querySelectorAll('.filter.is-open').forEach((filter) => {
    filter.classList.remove('is-open')
  })
})


function initDropdowns(container) {
  container = container || document;
  let dropdowns = container.querySelectorAll("[data-menu-dropdown]");
  dropdowns.forEach((dropdown) => {
    let trigger = dropdown.firstElementChild;
    if (!trigger) return;
    trigger.addEventListener("click", (e) => {
      e.preventDefault();
      dropdown.classList.toggle("is_open");
    });
  });
  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-menu-dropdown]")) return;
    dropdowns.forEach((d) => d.classList.remove("is_open"));
  });
}

function runDropdowns() {
  initDropdowns();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", runDropdowns);
} else {
  runDropdowns();
}

let sortBtns = document.querySelectorAll('.btn-sort')
sortBtns.forEach(btn => {
  btn.addEventListener('click', function () {
    console.log('q');
    btn.parentElement.querySelector('.sort').classList.toggle('is-open');
  })
})

document.addEventListener('click', function (e) {
  if (e.target.closest('.btn-sort') || e.target.closest('.sort')) return
  document.querySelectorAll('.sort.is-open').forEach((sort) => {
    sort.classList.remove('is-open')
  })
})
