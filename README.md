
## Badge

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/199b3f9a83e146e0b906cb8bcc315471)](https://app.codacy.com/gh/devperez/ToDoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)
# ToDoList

This is project #8 on my path to become a symfony developer following OpenClassRooms course.



## Installation

To install ToDoList, follow these steps:

1. Clone the repo:
```bash
  git clone https://github.com/devperez/ToDoList.git
```

2. Install dependencies:
Once in the ToDoList folder :
```bash
  composer install
```

3. Create the data base:
First, update your .env file with your own details, then :
```bash
  php bin/console doctrine:database:create
```

4. Execute migrations:
```bash
  php bin/console doctrine:migrations:migrate
```

5. Execute fixtures:
```bash
  php bin/console doctrine:fixtures:load
```

6. Launch the app:
```bash
  symfony serve
```

## Before contributing

Read the contrib.md file.