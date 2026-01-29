# CAPAAdmin

CAPAAdmin is a custom administration system built for managing baseball tournaments, players, games, and statistics.

## Features

- **Tournament Management**: Create and manage tournaments.
- **Player Registry**: Maintain a database of players.
- **Game Tracking**: Record game details and player statistics.
- **Reporting**: Generate reports and view dashboard statistics.

## Getting Started

1.  **Clone the repository**
2.  **Install dependencies**:
    ```bash
    composer install
    npm install
    ```
3.  **Setup Environment**:
    - Copy `.env.example` to `.env`
    - Configure database settings
    - Run `php artisan key:generate`
4.  **Migrate Database**:
    ```bash
    php artisan migrate
    ```
5.  **Build Assets**:
    ```bash
    npm run build
    ```

## License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
