import { Controller } from "@hotwired/stimulus";
import { instance } from "@viz-js/viz";

import { stimulus } from "../stimulus";

import type { RenderOptions } from "@viz-js/viz";

let instancePromise: null | ReturnType<typeof instance> = null;
const defaults: RenderOptions = {
  edgeAttributes: {
    color: "white",
    fontcolor: "white",
    fontname: "inherit",
    fontsize: 14,
  },
  graphAttributes: {
    bgcolor: "transparent",
    color: "#555",
    fontcolor: "white",
    fontname: "inherit",
    fontsize: 14,
  },
  nodeAttributes: {
    color: "white",
    fontcolor: "white",
    fontname: "inherit",
    fontsize: 14,
    margin: 0.1,
  },
};

@stimulus("graphviz")
export class controller_graphviz extends Controller<HTMLElement> {
  public static targets = ["scene"];
  public static values = {
    dotScript: String,
  };

  private declare readonly dotScriptValue: string;
  private declare readonly sceneTarget: HTMLElement;

  public async connect(): Promise<void> {
    if (this.element.classList.contains("fenced-graphviz--rendered")) {
      return;
    }

    const viz = await controller_graphviz.getInstance();
    const renderedNode = viz.renderSVGElement(
      String(this.sceneTarget.textContent),
      defaults,
    );

    this.sceneTarget.replaceChildren(renderedNode);
    this.element.classList.add("fenced-graphviz--rendered");
  }

  private static async getInstance(): ReturnType<typeof instance> {
    if (instancePromise) {
      return instancePromise;
    }

    instancePromise = instance();

    return instancePromise;
  }
}
