Opauth-Yammer
=============
[Opauth][1] strategy for Yammer authentication.

Implemented based on https://developer.yammer.com/authentication/ using OAuth2.

Opauth is a multi-provider authentication framework for PHP.

Demo: http://opauth.org/#yammer

Getting started
----------------
1. Install Opauth-Yammer:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/andrej-griniuk/opauth-yammer.git Yammer
   ```

2. Register a Yammer application at http://www.yammer.com/client_applications
   - Enter Website as your application URL (this can be outside of Opauth)
   - Redirect URI: enter `http://path_to_opauth/yammer/oauth2callback`

3. Configure Opauth-Yammer strategy with `client_id` and `client_secret`.

4. Direct user to `http://path_to_opauth/yammer` to authenticate


Strategy configuration
----------------------

Required parameters:

```php
<?php
'Yammer' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

License
---------
Opauth-Yammer is MIT Licensed
Copyright © 2014 Andrej Griniuk

[1]: https://github.com/uzyn/opauth