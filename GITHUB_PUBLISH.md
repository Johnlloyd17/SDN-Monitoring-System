# Publish This Project to GitHub (Manual Steps)

Run these commands one by one inside this project folder
(`C:\xampp\htdocs\Copy of SDN MS\v8-10-2026\SDN Monitoring System`).

## 1. Initialize Git and commit the project

```powershell
git init -b main
git add -A
git commit -m "Initial commit: SDN Monitoring System"
```

## 2. Publish without the unnecessary files (.gitignore)

`.gitignore` in this folder is applied automatically: `git add -A` skips
everything listed in it (`node_modules/`, `vendor/`, `.env`, `uploads/*`,
`*.log`, OS/editor files, etc.).

```powershell
# Preview ONLY the files that will be staged (nothing is written yet)
git add -An

# Verify ignored files are excluded (they show with !! marks)
git status --ignored

# Check why a specific file is ignored
git check-ignore -v path/to/file.pdf

# Stage + commit the filtered set
git add -A
git commit -m "Initial commit: SDN Monitoring System"

# Push (or create the repo first with the gh command below)
git push
```

> If a file you want is being skipped, it matches a `.gitignore` rule —
> remove or adjust that rule, or force-add it once with `git add -f <file>`.
> If something unnecessary is already tracked, untrack it (keeps the local
> file): `git rm -r --cached <file>` then commit.

## 3. Create the GitHub repository (public)

```powershell
gh repo create SDN-Monitoring-System --public --source . --remote origin --push
```

> This creates the repo on GitHub, links it as `origin`, and pushes everything.

## 4. Verify it was pushed

```powershell
git status -sb
git log --oneline -1
```

You should see `## main...origin/main` (meaning local and remote are in sync).

## Final URL

https://github.com/Johnlloyd17/SDN-Monitoring-System

## Pull latest updates from GitHub

```powershell
# Pull latest changes from remote
git pull origin main

# If you have local changes that conflict, stash them first
git stash
git pull origin main
git stash pop
```

## Useful extra commands

```powershell
# Push a new change after editing files
git add -A
git commit -m "your message here"
git push

# Check status
git status

# View recent commits
git log --oneline -5

# See what files changed
git diff

# Discard all local changes (reset to last commit)
git checkout .
git clean -fd

# Make the repository private instead of public
gh repo edit SDN-Monitoring-System --visibility private

# Make the repository public
gh repo edit SDN-Monitoring-System --visibility public
```
