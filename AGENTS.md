# Tempel release workflow

After creating and validating every new release zip, also deploy that exact zip to the Local test site at:

`/Users/dorusbos/Local Sites/dataspark/app/public/wp-content/plugins/tempel-settings`

Use `php tools/deploy-dataspark.php`. This deployment may require filesystem approval because the Local Sites directory is outside the repository. Verify the installed `TEMPEL_SETTINGS_VERSION` afterwards.

After the Local deployment and release checks succeed, deploy the same validated release to the production site through Cyberduck:

- Cloudways application: `studiotempel.nl` (application ID `4214066`)
- Cloudways server: `VPS-7`
- Cyberduck bookmark: `studiotempel` (`studiotempel@167.99.214.122`)
- Remote plugin directory: `/public_html/wp-content/plugins/tempel-settings`

Upload the extracted `tempel-settings` directory from the exact release zip and overwrite matching files. Do not upload uncommitted working-tree files directly. Confirm that the transfer finishes successfully and reopen the remote `tempel.php` to verify both the plugin header version and `TEMPEL_SETTINGS_VERSION`.
