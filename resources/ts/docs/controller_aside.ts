import { Controller } from "@hotwired/stimulus";

import { stimulus } from "../stimulus";

@stimulus("aside")
export class controller_aside extends Controller<HTMLElement> {
  public connect(): void {
    const activeLink = this.element.querySelector("a.active");

    if (!(activeLink instanceof HTMLAnchorElement)) {
      return;
    }

    activeLink.scrollIntoView({
      behavior: "auto",
      block: "center",
      inline: "center",
    });
  }
}
