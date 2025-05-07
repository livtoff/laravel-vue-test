# Laravel + Vue Starter Kit

This template project is based off the official [laravel-vue-starter-kit](https://github.com/laravel/vue-starter-kit). It has been (opinionately) optimized for a better Livtoff developer experience:

## It assumes you use the following technologies:

- Laravel Herd for local development
- PostgresSQL as the database driver, with root as the username and no password

## The following extra packages has been added

- Horizon, for queues
- Wayfinder for using routes in Vue
- Laravel CSP, for applying and managing content security policies
- Laravel Data, for the use of DTO's
- Laravel typescript transformer for making DTO's and enums available in Vue
- Laravel Telescope + toolbar, for debugging purposes
- Larastan, for static analysis
- Whisky, for setting up git hooks
- Route maker, for managing your routes from your controllers

## Editor

It assumes you're using Cursor. Basic rules have been setup in `.cursor/rules`

## Getting started

1. Run `php setup.php` this should guide you through all steps necessary to get started fast.
