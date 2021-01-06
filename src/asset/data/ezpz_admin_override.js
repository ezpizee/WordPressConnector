var EzpzOverrideEndpoints = {
  "refresh_token":"/wp-admin/admin.php?page=ezpizee-portal&view=api&endpoint=/api/v1/wordpress/refresh/token",
  "expire_in":"/wp-admin/admin.php?page=ezpizee-portal&view=api&endpoint=/api/v1/wordpress/expire-in",
  "get_auth":"/wp-admin/admin.php?page=ezpizee-portal&view=api&endpoint=/api/v1/wordpress/authenticated-user",
  "loginPageRedirectUrl":"{loginPageRedirectUrl}",
  "installPageRedirectUrl":"/administrator/index.php?option=com_ezpz&view=install",
  "csrfToken": "/wp-admin/admin.php?page=ezpizee-portal&view=api&endpoint=/api/v1/wordpress/crsf-token",
  "scriptUrlRegex": /^(?:http|https):\/\/[^/]+(\/.*)\/(\/wp-content\/plugins\/ezpizee\/).*\.js(\?.*)?$/
};
