import { Controller } from "@hotwired/stimulus";
import mermaid from "mermaid";

import { stimulus } from "../stimulus";

mermaid.initialize({
  fontFamily: "var(--font-family-body)",
  fontSize: 20,
  startOnLoad: false,
  theme: "base",
});

@stimulus("mermaid")
export class controller_mermaid extends Controller<HTMLElement> {
  public static targets = ["scene"];
  public static values = {
    dotScript: String,
  };

  private declare readonly dotScriptValue: string;
  private declare readonly sceneTarget: HTMLElement;

  public async connect(): Promise<void> {
    if (this.element.classList.contains("fenced-renderable--rendered")) {
      return;
    }

    await mermaid.run({
      nodes: [
        this.sceneTarget,
      ],
    });

    this.element.classList.add("fenced-renderable--rendered");
  }
}
