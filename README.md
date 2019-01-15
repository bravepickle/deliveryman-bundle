This is a Symfony bundle wrapper for Deliveryman library

## Installation
1. Install dependencies
```bash
composer create-project symfony/skeleton your-new-project
composer require bravepickle/deliveryman-bundle serializer
```
1. Configure
Ensure bundle is enabled in Kernel, e.g. (bundles.php)
```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    DeliverymanBundle\DeliverymanBundle::class => ['all' => true],
    // ...
];
```

Add BatchController as service (optional) to services.yaml
```yaml
    # ...
    DeliverymanBundle\Controller\BatchController:
        tags: ['controller.service_arguments']

```

Add routing (routes.yaml)
```yaml
# ...
batch:
    path: /batch
    controller: DeliverymanBundle\Controller\BatchController::httpGraph

```

Add to `config/packages` file `deliveryman.yaml` with your configuration, e.g.
```yaml
deliveryman:
    instances: 
        default:      # custom settings for default instance
            domains: [localhost]
        http_example: # add configuration instance to handle example.com domains specifically
            domains: [example.com, www.example.com, ex.com]
            channels:
                http_graph:
                    expected_status_codes: [200] # only these status codes allowed
```