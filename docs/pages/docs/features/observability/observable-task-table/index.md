---
collections: 
    - documents
layout: dm:document
parent: docs/features/observability/index
title: Observable Task Table
description: >
    Observable task table is a shared memory table that stores the status of 
    observable tasks. It is used to observe the status of long-running tasks.
---

# Observable Task Table

https://www.youtube.com/watch?v=Ac5Ww4PBPlY

Observable task table is a shared memory table that stores the status of 
observable tasks. It is used to observe the status of long-running tasks.

# Usage

## Updating Status of Observable Task

Observable tasks can be observed by the `ObservableTaskTable` service. 

The `ObservableTask` class is used to define the task to be observed. It's
callback function needs to return a generator. The generator should yield 
`ObservableTaskStatusUpdate` instances to update the status of the task.

```php
$this->observableTaskTable->observe(new ObservableTask(
    iterableTask: function (): Generator {
        yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Running, null);

        Coroutine::sleep(3);

        yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Finished, null);
    },
    name: 'test',
    category: 'test_tasks',
));
```

## Observing Task with Timeout

The `ObservableTaskTimeoutIterator` class is used to define a timeout for the 
task. If the task is inactive for the specified time, the task will be 
cancelled.

You can use it by composing it with an observable task:

```php
$observableTaskTimeout = new ObservableTaskTimeoutIterator(
    iterableTask: function () use (
        $webSocketAuthResolution,
        $webSocketConnection,
        $rpcRequest,
    ): Generator {
        yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Running, null);

        Coroutine::sleep(3);

        yield new ObservableTaskStatusUpdate(ObservableTaskStatus::Finished, null);
    },
    inactivityTimeout: 5.0,
);

$this->observableTaskTable->observe(new ObservableTask(
    iterableTask: $observableTaskTimeout,
    name: 'test',
    category: 'test_tasks',
));
```

# Rendering

Observable tasks table is iterable and uses shared memory. It is possible to
iterate over it in any process to get the accurate state of currently running
tasks.

```php file:app/HttpResponder/ObservableTasksDashboard.php
#[RespondsToHttp(
    method: RequestMethod::GET,
    pattern: '/observable_task_table',
)]
#[Singleton(collection: SingletonCollection::HttpResponder)]
final readonly class ObservableTasksDashboard extends HttpResponder
{
    public function __construct(
        private ObservableTaskTable $observableTaskTable,
    ) {}

    public function respond(ServerRequestInterface $request, ResponseInterface $response): HttpInterceptableInterface
    {
        return new TwigTemplate('observable_task_table.twig',[
            'observableTaskTable' => $this->observableTaskTable,
        ]);
    }
}
```

```twig file:app/views/observable_task_table.twig
<table>
    <thead>
        <tr>
            <th>slot</th>
            <th>status</th>
            <th>category</th>
            <th>name</th>
            <th>last update</th>
        </tr>
    </thead>
    <tbody>
        {% for slotId, observableTask in observableTaskTable %}
            <tr>
                <td>{{ slotId }}</td>
                <td>{{ observableTask.observableTaskStatusUpdate.status.value }}</td>
                <td>{{ observableTask.category }}</td>
                <td>{{ observableTask.name }}</td>
                <td>{{ observableTask.modifiedAt|intl_format_date(request) }}</td>
            </tr>
        {% endfor %}
    </tbody>
</table>
```
