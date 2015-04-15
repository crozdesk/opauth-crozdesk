# opauth-crozdesk

Opauth strategy for [Crozdesk](https://crozdesk.com) authentication

## Getting started

1. Install opauth-crozdesk:
  ```bash
  cd path_to_opauth/Strategy
  git clone https://github.com/crozdesk/opauth-crozdesk.git Crozdesk
  ```

2. Go to https://crozdesk.com/users/developers, grab the API key and secret and make sure the callback url points to `http://path_to_opauth/crozdesk/oauth2callback`

3. Configure the Crozdesk strategy in `opauth.conf.php`

## Opauth strategy configuration

Required parameters:

```php
<?php
$config = array(
  ... # Opauth settings like path/callback_url, etc

  'Strategy' => array(
    'Crozdesk' => array(
      'client_id' => 'API KEY',
      'client_secret' => 'API SECRET'
    ),
  ),
);
?>
```

## References
- [Opauth](https://github.com/opauth/opauth)
- [Crozdesk](https://crozdesk.com)
