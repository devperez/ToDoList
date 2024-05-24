Contribution guide
==================

### How to contribute to the project

1. Create a branch

```bash
    git checkout -b newBranchName
```
2. Write your changes

3. Once done and before pushing your changes:

    3.1. Use ECS:

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
    3.4 Pull Request
    - Once your new branch is pushed, go to GitHub and select the repo's project.
    - Click on "Pull Request".
    - Then click on "New Pull Request".
    - Select the local branch you just pushed (newBranchName in this example) as source branch.
    - Select the main branch of the project as target branch.
    - Review the changes you made and click on "Create Pull Request".
    - Fill in the details of your Pull Request, give it a title and a description.
    - Submit your Pull Request by clicking on "Create Pull Request".

> Once submitted, your Pull Request will be reviewed. It will probably be commented or accepted and merged with the main branch.