import type { LoggerInterface } from "./LoggerInterface";

export class Logger implements LoggerInterface {
  public constructor(private readonly contextName: string) {}

  public debug(...messages: Array<unknown>): void {
    console.debug(...this.format(...messages));
  }

  public error(...messages: Array<unknown>): void {
    console.error(...this.format(...messages));
  }

  public info(...messages: Array<unknown>): void {
    console.info(...this.format(...messages));
  }

  public log(...messages: Array<unknown>): void {
    console.log(...this.format(...messages));
  }

  public ns(contextName: string): LoggerInterface {
    return new Logger(`${this.contextName}.${contextName}`);
  }

  public success(...messages: Array<unknown>): void {
    console.log(...this.format(...messages));
  }

  public warn(...messages: Array<unknown>): void {
    console.warn(...this.format(...messages));
  }

  private format(...messages: Array<unknown>): Array<unknown> {
    return [`${this.contextName}\n`, ...messages];
  }
}
