import { Logger } from "./Logger";

import type { LoggerInterface } from "./LoggerInterface";

export function createLogger(contextName: string): LoggerInterface {
  return new Logger(contextName);
}
