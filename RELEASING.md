# Releasing tiendanube-php-sdk

1. Check the Semantic Versioning page for info on how to version the new release: [http://semver.org](http://semver.org)

2. Ensure your local repo is up-to-date

   ```bash
   git checkout master && git pull
   ```

3. Add an entry for the new release to `CHANGELOG.md`, and/or move the contents from the _Unreleased_ to the new release

4. Increment the version in `src/Context.php`

5. Stage the `CHANGELOG.md` and `src/Context.php` files

   ```bash
   git add CHANGELOG.md src/Context.php
   ```

6. To update the version, commit and push the changes and create the appropriate tag - Packagist will pick it up and release it
