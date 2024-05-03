Contribution guide
==================

### How to contribute to the project

1. Create a branch
```bash
    git checkout -b newBranchName
```
2. Write your changes

3. Once done and before pushing your changes :
    3.1 Use ECS:
    ```bash
        vendor/bin/ecs --fix
    ```
    3.2 Use Psalm:
    ```bash
        ./vendor/bin/psalm
    ```
    3.3 Correct your code and then push it from your local branch to the repo:
    ```bash
        git status
        git add <impacted files>
        git commit -m "commit message"
        git push
    ```