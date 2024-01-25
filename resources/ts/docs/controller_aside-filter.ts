import { Controller } from "@hotwired/stimulus";

import { stimulus } from "../stimulus";

@stimulus("aside-filter")
export class controller_aside_filter extends Controller<HTMLElement> {
  public static classes = ["filteredOutLink"];
  public static targets = ["filterableLink", "searchInput"];

  private declare readonly filterableLinkTargets: Array<HTMLAnchorElement>;
  private declare readonly filteredOutLinkClass: string;
  private declare readonly searchInputTarget: HTMLInputElement;

  public onInputChange(): void {
    const searchValue: string = this.searchInputTarget.value.toLowerCase();

    for (const link of this.filterableLinkTargets) {
      if (link.textContent?.toLowerCase().includes(searchValue)) {
        link.classList.remove(this.filteredOutLinkClass);
      } else {
        link.classList.add(this.filteredOutLinkClass);
      }
    }
  }
}
