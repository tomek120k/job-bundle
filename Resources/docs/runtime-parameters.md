Runtime Parameters
==================

Jobs can be executed with two types of parameters: serializable parameters and runtime parameters. Runtime parameters are provided from a context. They can be added to this context by registering an event listener.

## Default Runtime Parameters

The AbcJobBundle provides the default parameters `@abc.logger` and `@abc.manager`. If specified an instance of `Psr\Log\LoggerInterface` respectively `Abc\Abc\Bundle\JobBundle\Job\ManagerInterface` is injected into the job.

```php
namespace My\Bundle\ExampleBundle\Job\MyJob;

use Abc\Bundle\JobBundle\Annotation\JobParameters;
use Abc\Bundle\JobBundle\Job\ManagerInterface;

class ManagerAwareJob
{
    /**
     * @ParamType("manager", type="@abc.manager")
     */
    public function sayHello(ManagerInterface $manager)
    {
        $manager->...
    }
}
```

## Custom Runtime Parameters

To inject a custom runtime parameter you need to do two things:

1. Create a listener class
2. Register the listener class in the service container

## Step 1: Create a listener class

First you have to create the listener class that sets the runtime parameter into the execution context of a job:

```php
class MyJobListener
{
    private $factory;

    // ...

    public function onPreExecute(ExecutionEvent $event)
    {
        $parameter = $this->factory->create('something');

        $event->getContext()->set('my_custom_param', $parameter);
    }
}
```

The event passed to the listener is of type [ExecutionEvent](../../Event/ExecutionEvent.php) which provides access to the execution context of a job. This is a simple container where you can register parameters with a certain key.

## Step 2: Register the listener class in the service container

Next you need to register the listener in the service container and tag it:

```xml
<service id="my_job_listener" class="MyJobListener" public="true">
    <argument type="service">...</argument>
    <tag name="abc.job.event_listener" event="abc.job.pre_execute" method="onPreExecute"/>
</service>
```

You can now use this runtime parameter inside your jobs:

```php
/**
 * @ParamType("myParam", type="@my_custom_param")
 */
public function doSomething($myParam) {
}
```