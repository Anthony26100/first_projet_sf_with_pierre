import { Flipper, spring } from "flip-toolkit";
import { debounce } from "lodash";
import visibilityArticles from "./switchVisibilityArt";
/**
 * Class filter for search posts in ajax
 *
 * @property {HTMLElement} pagination - The pagination element
 * @property {HTMLElement} sortable - The sortable element
 * @property {HTMLElement} content - The content element
 * @property {HTMLElement} count - The count element
 * @property {HTMLFormElement} form - The form element
 * @property {number} page - The page number
 * @property {bool} moreNav - if the navigation is with button show more
 */
export default class Filter {
  /**
   *
   * @param {HTMLElement} element - the parent element of the page of search
   *
   */
  constructor(element) {
    if (element == null) {
      return;
    }
    this.pagination = element.querySelector(".js-filter-pagination");
    this.sortable = element.querySelector(".js-filter-sortable");
    this.content = element.querySelector(".js-filter-content");
    this.count = element.querySelector(".js-filter-count");
    this.form = element.querySelector(".js-filter-form");
    this.page = parseInt(
      new URLSearchParams(window.location.search).get("page") || 1
    );
    this.moreNav = this.page == 1;
    this.bindEvents();
  }

  /**
   * Add actions to the elements
   */
  bindEvents() {
    const linkClickListener = (e) => {
      // Si l'élément est une balise <a></a> Ou une balise <i></i>
      if (e.target.tagName === "A" || e.target.tagName === "I") {
        e.preventDefault(); // casse l'utilisation normal d'une balise/lien <a></a> ou <i></i> (button)

        let url = "";

        if (e.target.tagName === "I") {
          url = e.target.parentNode.parentNode.getAttribute("href");
        } else {
          url = e.target.getAttribute("href");
        }
        this.loadUrl(url);
      }
    };

    if (this.moreNav) {
      this.pagination.innerHTML = `<button class="btn btn-warning mt-2 btn-show-more">Voir Plus</button>`;
      this.pagination
        .querySelector("button")
        .addEventListener("click", this.loadMore.bind(this));
    } else {
      this.pagination.addEventListener("click", linkClickListener);
    }

    this.sortable.addEventListener("click", (e) => {
      linkClickListener(e);
    });

    /* Actions sur le formulaire */
    this.form.querySelectorAll('input[type="text"]').forEach((input) => {
      input.addEventListener("keyup", debounce(this.loadForm.bind(this), 400));
    });

    this.form.querySelectorAll('input[type="checkbox"]').forEach((input) => {
      input.addEventListener("change", debounce(this.loadForm.bind(this), 800));
    });
  }

  /**
   * Load more element on the page
   */
  async loadMore() {
    const button = this.pagination.querySelector("button");
    button.setAttribute("disabled", "disabled");
    this.page++;
    const url = new URL(window.location.href); // url actuel du navigateur
    const params = new URLSearchParams(url.search); // .search recupere les derniers params
    params.set("page", this.page); // dans l'url on definit ou redefinit notre page exemple (page=this.page soit page=2 ou 1 ou autres)
    await this.loadUrl(url.pathname + "?" + params.toString(), true);
    button.removeAttribute("disabled");
  }

  async loadForm() {
    this.page = 1;

    const data = new FormData(this.form);
    const url = new URL(
      this.form.getAttribute("action") || window.location.href
    );
    const params = new URLSearchParams();
    data.forEach((value, key) => {
      params.append(key, value);
    });

    return this.loadUrl(url.pathname + "?" + params.toString());
  }

  async loadUrl(url, append = false) {
    this.showLoader();
    const params = new URLSearchParams(url.split("?")[1] || "");
    params.set("ajax", 1);

    // const response = await fetch(url.split("?")[0] + "?" + params.toString(), {
    //   headers: {
    //     "X-Requested-With": "XMLHttpRequest", // <-- Request en ajax
    //   },
    // });
    const response = await fetch(`${url.split("?")[0]}?${params.toString()}`, {
      headers: {
        "X-Requested-With": "XMLHttpRequest", // <-- Request en ajax
      },
    });

    if (response.status >= 200 && response.status < 300) {
      const data = await response.json();

      this.flipContent(data.content, append);

      if (!this.moreNav) {
        this.pagination.innerHTML = data.pagination;
      } else if (
        this.page == data.pages ||
        this.content.children.item(0) ===
          this.content.children.namedItem("article-no-response")
      ) {
        this.pagination.style.display = "none";
      } else {
        this.pagination.style.display = "block";
      }

      this.sortable.innerHTML = data.sortable;
      this.count.innerHTML = data.count;

      // this.form.innerHTML = data.form;
      params.delete("ajax");
      history.replaceState({}, "", `${url.split("?")[0]}?${params.toString()}`); // remplace l'url et la mise à jours
    } else {
      console.error(response);
    }
    this.hideLoader();
  }

  /**
   * Replace all posts card with animation
   */
  flipContent(content, append) {
    const springName = "veryGentle";
    const exitSpring = function (element, index, onComplete) {
      spring({
        config: "stiff",
        values: {
          translateY: [0, -25],
          opacity: [1, 0],
        },
        onUpdate: ({ translateY, opacity }) => {
          element.style.opacity = opacity;
          element.style.transform = `translateY(${translateY}px)`;
        },
        onComplete,
      });
    };

    // Apparition des cards
    const appearSpring = function (element, index) {
      spring({
        config: "stiff",
        values: {
          translateY: [0, 25],
          opacity: [0, 1],
        },
        onUpdate: ({ translateY, opacity }) => {
          element.style.opacity = opacity;
          element.style.transform = `translateY(${translateY}px)`;
        },
        delay: index * 15,
      });
    };

    const flipper = new Flipper({ element: this.content });
    let cards = this.content.children;

    for (let card of cards) {
      flipper.addFlipped({
        element: card,
        flipId: card.id,
        shouldFlip: false,
        spring: springName,
        onExit: exitSpring,
      });
    }
    flipper.recordBeforeUpdate();

    if (append) {
      this.content.innerHTML += content;
    } else {
      this.content.innerHTML = content;
    }

    cards = this.content.children;
    for (let card of cards) {
      flipper.addFlipped({
        element: card, // elements enfants
        flipId: card.id, // recuperer l'id sur le html exemple avec twig {{ article.id }}
        spring: springName,
        onAppear: appearSpring,
      });
    }

    flipper.update();
    visibilityArticles();
  }

  /* Showloader */
  showLoader() {
    this.form.classList.add("is-loading");
    const loader = this.form.querySelector(".js-loading");

    if (loader == null) {
      return;
    }

    loader.setAttribute("aria-hidden", false);
    loader.style.display = null;
  }

  /* Hidden Loader */
  hideLoader() {
    this.form.classList.remove("is-loading");
    const loader = this.form.querySelector(".js-loading");

    if (loader == null) {
      return;
    }

    loader.setAttribute("aria-hidden", true);
    loader.style.display = "none";
  }
}
