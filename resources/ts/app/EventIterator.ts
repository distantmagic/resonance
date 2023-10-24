import type { LoggerInterface } from "./LoggerInterface";

class IteratorClosed extends Error {}

export abstract class EventIterator<TEvent> implements AsyncIterator<TEvent> {
  protected abstract attach(this: EventIterator<TEvent>): void;
  protected abstract detach(this: EventIterator<TEvent>): void;

  public isClosed: boolean = false;

  protected cancelEvent: null | ((error: IteratorClosed) => void) = null;
  protected eventsBuffer: Array<TEvent> = [];
  protected nextEvent: null | ((evt: TEvent) => void) = null;

  public constructor(protected readonly logger: LoggerInterface) {}

  public [Symbol.asyncIterator](): AsyncIterator<TEvent> {
    return this;
  }

  public [Symbol.iterator](): never {
    throw new Error("EventIterator is only an async iterator.");
  }

  public close(): void {
    this.detach();
    this.isClosed = true;
    this.cancelEvent?.(new IteratorClosed());
  }

  public handleEvent(this: EventIterator<TEvent>, evt: TEvent): void {
    if (this.nextEvent) {
      this.nextEvent(evt);
      return;
    }

    if (this.eventsBuffer.length > 0) {
      this.logger.warn(
        "Event queue is backing up. Currently has",
        this.eventsBuffer.length,
        "unprocessed",
        this.eventsBuffer.length > 1 ? "events." : "event.",
      );
    }

    if (this.eventsBuffer.length > 20) {
      this.logger.error("Too many backed up events. Closing iterator.");
      this.close();
      return;
    }

    this.eventsBuffer.push(evt);
  }

  public async next(
    this: EventIterator<TEvent>,
  ): Promise<IteratorResult<TEvent>> {
    try {
      return {
        done: false,
        value: await this.awaitNextEvent(),
      };
    } catch (error) {
      if (!(error instanceof IteratorClosed)) {
        this.logger.error(error);
      }

      this.close();

      return {
        done: true,
        value: null,
      };
    }
  }

  public return(
    this: EventIterator<TEvent>,
    value?: unknown,
  ): Promise<IteratorReturnResult<unknown>> {
    this.close();

    return Promise.resolve({
      done: true,
      value: value,
    } as const);
  }

  private async awaitNextEvent(this: EventIterator<TEvent>): Promise<TEvent> {
    const enqueuedEvent = this.eventsBuffer.shift();

    if (enqueuedEvent) {
      return enqueuedEvent;
    }

    const evt = await new Promise<TEvent>((resolve, reject) => {
      this.cancelEvent = reject;
      this.nextEvent = resolve;
    });

    this.cancelEvent = null;
    this.nextEvent = null;

    return evt;
  }
}
