# Publish This Project to GitHub (Manual Steps)

Run these commands one by one inside this project folder
(`C:\xampp\htdocs\Copy of SDN MS\v8-10-2026\SDN Monitoring System`).

## 1. Initialize Git and commit the project

```powershell
git init -b main
git add -A
git commit -m "Initial commit: SDN Monitoring System"
```

## 2. Create the GitHub repository (public)

```powershell
gh repo create SDN-Monitoring-System --public --source . --remote origin --push
```

> This creates the repo on GitHub, links it as `origin`, and pushes everything.

## 3. Verify it was pushed

```powershell
git status -sb
git log --oneline -1
```

You should see `## main...origin/main` (meaning local and remote are in sync).

## Final URL

https://github.com/Johnlloyd17/SDN-Monitoring-System

## Useful extra commands

```powershell
# Push a new change after editing files
git add -A
git commit -m "your message here"
git push

# Make the repository private instead of public
gh repo edit SDN-Monitoring-System --visibility private

# Make the repository public
gh repo edit SDN-Monitoring-System --visibility public
```
