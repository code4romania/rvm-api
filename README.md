# Resource & Volunteers Management App - API

[![GitHub contributors](https://img.shields.io/github/contributors/code4romania/rvm-api.svg?style=for-the-badge)](https://github.com/code4romania/rvm-api/graphs/contributors) [![GitHub last commit](https://img.shields.io/github/last-commit/code4romania/rvm-api.svg?style=for-the-badge)](https://github.com/code4romania/rvm-api/commits/master) [![License: MPL 2.0](https://img.shields.io/badge/license-MPL%202.0-brightgreen.svg?style=for-the-badge)](https://opensource.org/licenses/MPL-2.0)

API of the resource and volunteers management app of DSU (Dispeceratul pentru Situatii de Urgenta)

[See the project live](https://www.figma.com/proto/K7Qqywpx1QFVzG1ml2Fa3qsv/Resource-%26-Volunteer-Management-App?scaling=min-zoom)

DSU (Departamentul pentru Situatii de Urgenta) needs a digital tool to manage the resources it has at its disposal, their location, as well as the volunteers and NGOs that are registered to offer help during a crisis situation. The aim of this project is to offer a better management solution so that DSU is better prepared for an emergency situation.

[Contributing](#contributing) | [Built with](#built-with) | [Repos and projects](#repos-and-projects) | [Deployment](#deployment) | [Feedback](#feedback) | [License](#license) | [About Code4Ro](#about-code4ro)

## Contributing

This project is built by amazing volunteers and you can be one of them! Here's a list of ways in [which you can contribute to this project](.github/CONTRIBUTING.MD).

You can also list any pending features and planned improvements for the project here.

## Built With

### Programming languages

PHP/Laravel

### Platforms

Laravel

### Frontend framework

### Package managers

Composer

### Database technology & provider

MySQL

## Repos and projects

Client of the API: https://github.com/code4romania/rvm-client

## Development

    # To bootstrap the project (Run this only once), run the following commands in your shell:

    ./dev/composer.sh update
    cp src/.env.example src/.env
    docker-compose up
    ./dev/key_generate.sh
    ./dev/artisan.sh migrate
    ./dev/artisan.sh passport:install

    # Every other time

    docker-compose up

## Swagger
[L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) has been bundled which is a Laravel 5 - Swagger integration that
works out of the box.

To parse new API route definitions, you need to publish a new version of Swagger definitions

    ./dev/artisan.sh vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

To see all API definitions, hit the [/api/documentation](http://localhost:8080/api/documentation) endpoint of the server.
## Deployment

TBD

## Feedback

* Request a new feature on GitHub.
* Vote for popular feature requests.
* File a bug in GitHub Issues.
* Email us with other feedback contact@code4.ro

## License 

This project is licensed under the MPL 2.0 License - see the [LICENSE](LICENSE) file for details

## About Code4Ro

Started in 2016, Code for Romania is a civic tech NGO, official member of the Code for All network. We have a community of over 500 volunteers (developers, ux/ui, communications, data scientists, graphic designers, devops, it security and more) who work pro-bono for developing digital solutions to solve social problems. #techforsocialgood. If you want to learn more details about our projects [visit our site](https://www.code4.ro/en/) or if you want to talk to one of our staff members, please e-mail us at contact@code4.ro.

Last, but not least, we rely on donations to ensure the infrastructure, logistics and management of our community that is widely spread across 11 timezones, coding for social change to make Romania and the world a better place. If you want to support us, [you can do it here](https://code4.ro/en/donate/).
