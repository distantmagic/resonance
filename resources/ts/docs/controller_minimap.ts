import { Controller } from "@hotwired/stimulus";

import { createLogger } from "../app/createLogger";
import { MainLoopIterator } from "../app/MainLoopIterator";
import { stimulus } from "../stimulus";
import { controller_article } from "./controller_article";

@stimulus("minimap")
export class controller_minimap extends Controller<HTMLElement> {
  public static outlets = ["article"];
  public static targets = ["link", "track"];

  private declare readonly articleOutlet: controller_article;
  private declare readonly linkTargets: Array<HTMLAnchorElement>;
  private readonly logger = createLogger("controller_minimap");
  private mainLoopIterator: null | MainLoopIterator = null;
  private readonly permalinks = new Map<string, HTMLAnchorElement>();
  private declare readonly trackTarget: HTMLElement;

  public async connect(): Promise<void> {
    this.mainLoopIterator = new MainLoopIterator(this.logger);

    // eslint-disable-next-line no-empty-pattern
    for await (const {} of this.mainLoopIterator) {
      this.update();
    }
  }

  public disconnect(): void {
    this.mainLoopIterator?.close();
  }

  public linkTargetConnected(): void {
    this.mapLinkTargets();
  }

  public linkTargetDisconnected(): void {
    this.mapLinkTargets();
  }

  private mapLinkTargets(): void {
    this.permalinks.clear();

    for (const linkTarget of this.linkTargets) {
      const linkTargetUrl = new URL(linkTarget.href);

      this.permalinks.set(linkTargetUrl.hash.substring(1), linkTarget);
    }
  }

  private update(): void {
    for (const [permalink, linkTarget] of this.permalinks.entries()) {
      if (this.articleOutlet.visiblePermalinks.has(permalink)) {
        linkTarget.classList.add("visible");
      } else {
        linkTarget.classList.remove("visible");
      }
    }
  }
}
