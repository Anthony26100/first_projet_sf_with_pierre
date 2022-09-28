import { param } from "jquery";
import { debounce } from "lodash";
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
      if (append) {
        this.content.innerHTML += data.content;
      } else {
        this.content.innerHTML = data.content;
      }

      if (!this.moreNav) {
        this.pagination.innerHTML = data.pagination;
      } else if (this.page == data.pages) {
        this.pagination.style.display = "none";
      } else {
        this.pagination.style.display = null;
      }
      this.sortable.innerHTML = data.sortable;
      this.count.innerHTML = data.count;

      // this.form.innerHTML = data.form;
      params.delete("ajax");
      history.replaceState({}, "", `${url.split("?")[0]}?${params.toString()}`); // remplace l'url et mise à jours
    } else {
      console.error(response);
    }
    this.hideLoader();
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
