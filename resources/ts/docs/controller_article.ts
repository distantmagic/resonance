import { Controller } from "@hotwired/stimulus";

import { stimulus } from "../stimulus";

@stimulus("article")
export class controller_article extends Controller<HTMLElement> {
  public visibleHeadings = new Set<HTMLHeadingElement>();
  public visiblePermalinks = new Set<string>();

  private intersectionObserver: null | IntersectionObserver = null;

  public connect(): void {
    this.intersectionObserver = new IntersectionObserver(
      this.onIntersectionChanged.bind(this),
      {
        root: null,
      },
    );

    for (const heading of this.element.querySelectorAll("h1,h2,h3")) {
      this.intersectionObserver?.observe(heading);
    }
  }

  public disconnect(): void {
    this.intersectionObserver?.disconnect();
    this.intersectionObserver = null;
  }

  private onIntersectionChanged(
    entries: Array<IntersectionObserverEntry>,
  ): void {
    for (const entry of entries) {
      const target = entry.target;

      if (target instanceof HTMLHeadingElement) {
        if (entry.isIntersecting) {
          this.visibleHeadings.add(target);
        } else {
          this.visibleHeadings.delete(target);
        }
      }
    }

    this.visiblePermalinks.clear();

    for (const visibleHeading of this.visibleHeadings) {
      const permalink = visibleHeading.querySelector("[id].heading-permalink");

      if (
        permalink instanceof HTMLAnchorElement &&
        "string" === typeof permalink.id
      ) {
        this.visiblePermalinks.add(permalink.id);
      }
    }
  }
}
