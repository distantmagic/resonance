import { Controller } from "@hotwired/stimulus";
import hljs from "highlight.js/lib/core";
import css from "highlight.js/lib/languages/css";
import ini from "highlight.js/lib/languages/ini";
import json from "highlight.js/lib/languages/json";
import makefile from "highlight.js/lib/languages/makefile";
import markdown from "highlight.js/lib/languages/markdown";
import php from "highlight.js/lib/languages/php";
import phpTemplate from "highlight.js/lib/languages/php-template";
import protobuf from "highlight.js/lib/languages/protobuf";
import shell from "highlight.js/lib/languages/shell";
import twig from "highlight.js/lib/languages/twig";
import typescript from "highlight.js/lib/languages/typescript";
import yaml from "highlight.js/lib/languages/yaml";

import { stimulus } from "../stimulus";

hljs.registerLanguage("css", css);
hljs.registerLanguage("ini", ini);
hljs.registerLanguage("json", json);
hljs.registerLanguage("makefile", makefile);
hljs.registerLanguage("markdown", markdown);
hljs.registerLanguage("php", php);
hljs.registerLanguage("php-template", phpTemplate);
hljs.registerLanguage("protobuf", protobuf);
hljs.registerLanguage("shell", shell);
hljs.registerLanguage("twig", twig);
hljs.registerLanguage("typescript", typescript);
hljs.registerLanguage("yaml", yaml);

hljs.registerLanguage("graphql", function (e) {
  return {
    aliases: ["gql"],
    keywords: {
      keyword:
        "query mutation subscription|10 type input schema directive interface union scalar fragment|10 enum on ...",
      literal: "true false null",
    },
    contains: [
      e.HASH_COMMENT_MODE,
      e.QUOTE_STRING_MODE,
      e.NUMBER_MODE,
      {
        className: "type",
        begin: "[^\\w][A-Z][a-z]",
        end: "\\W",
        excludeEnd: !0,
      },
      {
        className: "literal",
        begin: "[^\\w][A-Z][A-Z]",
        end: "\\W",
        excludeEnd: !0,
      },
      {
        className: "variable",
        begin: "\\$",
        end: "\\W",
        excludeEnd: !0,
      },
      {
        className: "keyword",
        begin: "[.]{2}",
        end: "\\.",
      },
      {
        className: "meta",
        begin: "@",
        end: "\\W",
        excludeEnd: !0,
      },
    ],
    illegal: /([;<']|BEGIN)/,
  };
});

@stimulus("hljs")
export class controller_hljs extends Controller<HTMLElement> {
  public static values = {
    language: String,
  };

  private declare readonly languageValue: string;

  public connect(): void {
    if (this.element.classList.contains("hljs")) {
      // this means that Turbo cached an already highlighted element
      return;
    }

    const language = hljs.getLanguage(this.languageValue);

    if (!language || !language.name) {
      // highlighting failed, add .hljs class anyway to indicate that it's
      // done
      this.element.classList.add("hljs");

      return;
    }

    hljs.highlightElement(this.element);
  }
}
