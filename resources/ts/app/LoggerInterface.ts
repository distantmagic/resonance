type LoggingMethod = (...message: Array<unknown>) => void;

export interface LoggerInterface {
  debug: LoggingMethod;
  error: LoggingMethod;
  info: LoggingMethod;
  log: LoggingMethod;
  success: LoggingMethod;
  warn: LoggingMethod;

  ns(contextName: string): LoggerInterface;
}
