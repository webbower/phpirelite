# PHPireLite PHP Web Framework

## What is PHPireLite?

PHPireLite is an attempt to make PHP sexy. It is a Framework that isn't really a framework.

The core component of PHPireLite is its Kernel. It provides a simple, lightweight running environment for your app with a small handful of support code and conventions. From there, you can write your app however you want. There eventually will be MVC, ORM, Routing, advanced Form and HTTP support, and more as part of the framework package, but it's all opt-in beyond the Kernel. You could make a simple, single-page website without using anything beyond the Kernel.

## Getting Started

1. Create a folder to house your whole application.
2. From that folder, clone the Git repo to your dev environment.
3. Still from that folder, run `php phpirelite/bin/phpire.php init <env>` where `<env>` is the environement (`dev` [default], `stage`, or `prod`). This will configure the real CLI tool and create the Env file.
4. Still from that folder, run `phpirelite/bin/phpire app new <name>` where `<name>` is what you want to call your app.
5. Set the new `<name>/web` folder as your webroot.
6. Open the new webroot in your web browser. You should see the message about successfully creating the `<name>` App
7. Open `<name>/lib/<name>.php`. The `main()` method of the class is what generates that output. That's where you'll structure you're app's running logic. Change what gets echoed and refresh your web browser.