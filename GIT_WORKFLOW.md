# Git Workflow Guide - GitHub Desktop

## 🌳 Branch Strategy (Git Flow)

```
main (production)
 └── develop (primary development)
      ├── feature/user-auth
      ├── feature/shopping-cart
      └── feature/payment-processing
```

### Branch Naming Convention
```
feature/{feature-name}     → New features
bugfix/{bug-name}          → Bug fixes
hotfix/{issue-name}        → Critical fixes to main
```

---

## ✅ Step-by-Step Git Workflow

### STEP 1: Initial Setup with GitHub Desktop

1. **Open GitHub Desktop**
2. **Clone Repository**:
   - File → Clone Repository
   - Paste your GitHub URL
   - Choose local path: `c:\Users\shiva\Desktop\Haarlem-Project-Festival`
   - Click "Clone"

3. **Verify Branches**:
   - Should see `main` and `develop` branches

---

### STEP 2: Starting a New Feature (Sprint Work)

**Scenario**: Starting Shopping Cart feature (Sprint 3)

1. **Switch to develop branch**:
   - Click "Current Branch" at top
   - Select `develop`
   - Click "Fetch origin" to update

2. **Create feature branch**:
   - Click "New Branch" button
   - Name: `feature/shopping-cart`
   - Base branch: `develop`
   - Click "Create Branch"

3. **Publish branch**:
   - Click "Publish branch" (makes it visible on GitHub)

---

### STEP 3: Making Changes During Sprint

**Example**: Add cart functionality

1. **Make code changes** in VS Code
   - Add files: `src/Controllers/CartController.php`
   - Edit files: `routes/web.php`
   - Create views: `resources/views/cart/`

2. **View Changes in GitHub Desktop**:
   - You'll see modified files listed
   - Red = removed, Green = added, Orange = modified
   - Can click "view the" file to see exact changes

3. **Commit Changes** (Small commits are better):
   
   Example Commit 1:
   - Select modified files you want to commit
   - Summary: `Add CartController with add/remove methods`
   - Description: 
     ```
     - Implement addToCart() method
     - Implement removeFromCart() method
     - Add cart session management
     ```
   - Click "Commit to feature/shopping-cart"

   Example Commit 2 (Later):
   - Summary: `Create cart view template`
   - Description: `Add shopping cart page with item list and totals`
   - Click "Commit"

4. **Push Changes**:
   - Click "Push origin" to send commits to GitHub
   - GitHub Desktop will show upload progress

---

### STEP 4: During Sprint - Sync with develop

If team makes changes to `develop` while you're working:

1. **Fetch updates**:
   - Click "Fetch origin"

2. **Merge develop into feature branch**:
   - Click "Current Branch" → `feature/shopping-cart`
   - Click "Merge into current branch"
   - Select `develop` from dropdown
   - Click "Create a Merge Commit"
   - Resolve any conflicts if needed

---

### STEP 5: Completing Sprint - Create Pull Request

**After feature is complete & tested**:

1. **Ensure all code is committed & pushed**:
   - Click "Push origin" one final time

2. **Go to GitHub.com**:
   - Navigate to your repository
   - Go to "Pull Requests" tab
   - Click "New Pull Request"

3. **Create Pull Request**:
   - Base branch: `develop`
   - Compare branch: `feature/shopping-cart`
   - Title: `[SPRINT 3] Add Shopping Cart Functionality`
   - Description:
     ```
     ## Overview
     Implements shopping cart with add/remove items
     
     ## Changes
     - Add CartController
     - Create cart views
     - Implement session-based cart
     
     ## Testing
     - [x] Add items to cart
     - [x] Remove items from cart
     - [x] Cart displays correctly
     
     ## Security
     - [x] Input validation
     - [x] Session protection
     ```
   - Click "Create Pull Request"

4. **Review & Merge** (You can self-review for now):
   - Review changes in "Files Changed" tab
   - If ready, click "Merge pull request"
   - Select "Squash and merge" (cleans up history)
   - Confirm merge
   - Delete feature branch after merge

---

### STEP 6: Syncing Back to develop

After merging on GitHub:

1. **In GitHub Desktop**:
   - Switch to `develop` branch
   - Click "Fetch origin"
   - Click "Pull origin" to get merged changes
   - You now have the feature code in develop

2. **Delete old feature branch locally**:
   - Right-click `feature/shopping-cart`
   - Click "Delete"

---

## 📋 Sprint Workflow Summary

### Day 1-2 of Sprint:
```
1. Create feature branch from develop
2. Code & commit frequently
3. Push to GitHub
```

### Day 3 (End of Sprint):
```
1. Final testing & bug fixes
2. Make final commits
3. Push all changes
4. Create Pull Request
5. Review & Merge to develop
6. Update main branch (after all sprints complete)
```

---

## 🔄 Multi-Sprint Workflow

### After Completing Sprint 1 (User Auth):
```
develop (now has auth feature merged)
  ↓
Create feature/payment-integration
  ↓
Code & test
  ↓
PR to develop
  ↓
Merge
```

### Pattern for Each Sprint:
```
develop
  ↓ checkout new feature branch
feature/sprint-X-feature
  ↓ work for 3-4 days
  ↓ commit frequently
  ↓ create PR
develop
  ↓ merge PR
  ↓ repeat for next sprint
```

---

## 🚨 Important Rules

### ✅ DO:
- **Commit frequently** (every 1-2 hours)
- **Write clear commit messages** (future you will thank you)
- **Test before pushing**
- **Keep branches focused** (one feature per branch)
- **Create detailed PRs** with checklist
- **Delete feature branches after merging**

### ❌ DON'T:
- **Commit directly to develop** (always use feature branches)
- **Commit to main** (merge only from develop when done)
- **Wait days to push** (push daily at minimum)
- **Leave old branches** (delete after merge)
- **Mix multiple features** in one branch

---

## 📊 Commit Message Format

**Good Commit**:
```
Add shopping cart functionality

- Implement CartController with add/remove methods
- Create cart session management
- Add validation for cart items
- Update routes for cart pages
```

**Bad Commit**:
```
Update stuff
```

---

## 🐛 Handling Conflicts

If merging develop into your feature branch causes conflicts:

1. **GitHub Desktop will alert you**
2. **Open the conflicting file**:
   - You'll see:
     ```
     <<<<<<< feature/shopping-cart
     your code
     =======
     develop code
     >>>>>>>
     ```
3. **Manually choose which to keep**:
   - Keep your changes: delete the `<<<` markers and develop code
   - Or keep develop code: delete your changes
4. **Save the file**
5. **Commit the merge** in GitHub Desktop

---

## 📱 GitHub Desktop Quick Tips

### Useful Buttons:
- **Fetch**: Get latest from GitHub
- **Pull**: Fetch + Apply changes locally
- **Push**: Send commits to GitHub
- **Create Pull Request**: Opens browser to create PR
- **Merge**: Merge another branch into current

### Viewing Changes:
- Click **History** tab to see all commits
- Click a commit to see file changes
- Click **Changes** tab to see current uncommitted changes

---

## 🎯 Payment Feature Workflow (Your Focus)

When ready to start payment integration:

1. **Create branch**:
   ```
   feature/payment-processing
   ```

2. **Typical daily workflow**:
   - Open VS Code
   - Make changes to payment code
   - Commit: `Add Stripe payment integration`
   - Push: Click "Push origin"
   - End of day review in GitHub.com

3. **End of sprint**:
   - Create PR to develop
   - Link to requirements
   - Include test results
   - Merge to develop

---

## 📈 Measuring Progress (Scrum - 10% of Grade)

For each sprint, you'll document:
- **Sprint goal**: "Implement shopping cart"
- **User stories completed**
- **Days worked**: Track progress
- **Commits made**: ~2-3 commits per day = good
- **Testing performed**
- **Git proof**: PR shows all work merged

GitHub shows:
- Commit history (proof of work)
- PR descriptions (what was built)
- Merge timeline (sprint cadence)

---

## 🚀 Final Release Workflow

After all sprints are done and develop is stable:

1. **Create release branch**:
   - From develop
   - Name: `release/v1.0`

2. **Final testing & bug fixes**
   
3. **Merge to main**:
   - Create PR from release → main
   - Merge with "Create a merge commit"
   - Tag as v1.0

4. **Merge back to develop**:
   - Any final fixes go back to develop

---

## 📚 Reference Commands (If Using Terminal)

```bash
# Create & switch to feature branch
git checkout -b feature/shopping-cart

# See what changed
git status

# Stage changes (can do in GitHub Desktop instead)
git add .

# Commit
git commit -m "Add shopping cart"

# Push
git push origin feature/shopping-cart

# Switch branches
git checkout develop

# See all branches
git branch -a

# Delete local branch
git branch -d feature/shopping-cart
```

But you probably won't need these - GitHub Desktop handles it!

---

## ✨ Summary

**Your Git workflow each sprint**:
```
develop
   ↓ (create feature branch)
feature/sprint-X
   ↓ (code & commit daily)
feature/sprint-X (with 10+ commits)
   ↓ (create PR)
develop (PR review)
   ↓ (merge & test)
develop (feature now live)
```

Repeat for each sprint. After all sprints, merge develop → main for release.

