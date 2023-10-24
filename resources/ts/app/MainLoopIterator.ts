import { Clock } from "three";

import { EventIterator } from "./EventIterator";

import type { LoggerInterface } from "./LoggerInterface";

type MainLoopTickState = {
  delta: number;
  tick: number;
};

export class MainLoopIterator extends EventIterator<MainLoopTickState> {
  private readonly clock = new Clock(false);
  private currentTick: number = 0;

  public constructor(logger: LoggerInterface) {
    super(logger);

    this.onAnimationFrame = this.onAnimationFrame.bind(this);
    this.attach();
  }

  protected attach(): void {
    this.clock.start();
    // eslint-disable-next-line @typescript-eslint/unbound-method
    requestAnimationFrame(this.onAnimationFrame);
  }

  protected detach(): void {
    this.clock.stop();
  }

  private onAnimationFrame(this: MainLoopIterator): void {
    if (this.isClosed) {
      return;
    }

    this.handleEvent({
      delta: this.clock.getDelta(),
      tick: this.currentTick,
    });
    this.currentTick += 1;

    // eslint-disable-next-line @typescript-eslint/unbound-method
    requestAnimationFrame(this.onAnimationFrame);
  }
}
