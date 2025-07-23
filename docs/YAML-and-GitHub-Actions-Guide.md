# 📝 YAML and GitHub Actions Guide for Beginners

This guide will teach you **everything you need to know** about YAML files and how GitHub Actions uses them to automate your workflows.

## 📋 Table of Contents

1. [What is YAML?](#what-is-yaml)
2. [YAML Syntax Basics](#yaml-syntax-basics)
3. [How GitHub Actions Uses YAML](#how-github-actions-uses-yaml)
4. [Understanding Your Workflow Files](#understanding-your-workflow-files)
5. [Common Patterns and Examples](#common-patterns-and-examples)
6. [Tips for Writing YAML](#tips-for-writing-yaml)
7. [Troubleshooting YAML Errors](#troubleshooting-yaml-errors)

---

## 🤔 What is YAML?

**YAML** stands for **"YAML Ain't Markup Language"** (it's a recursive acronym!). Think of it as a way to write configuration files that are:

- **Human-readable** - Easy to read and understand
- **Structured** - Organized in a hierarchical way
- **Simple** - No complex syntax like XML or JSON

### YAML vs Other Formats

```yaml
# YAML (easy to read)
name: "John Doe"
age: 30
hobbies:
  - programming
  - gaming
  - reading
```

```json
// JSON (harder to read)
{
  "name": "John Doe",
  "age": 30,
  "hobbies": ["programming", "gaming", "reading"]
}
```

```xml
<!-- XML (very verbose) -->
<person>
  <name>John Doe</name>
  <age>30</age>
  <hobbies>
    <hobby>programming</hobby>
    <hobby>gaming</hobby>
    <hobby>reading</hobby>
  </hobbies>
</person>
```

**Winner: YAML** - Much cleaner and easier to understand! 🎉

---

## 📚 YAML Syntax Basics

### 1. Key-Value Pairs

```yaml
# Simple key-value pairs
name: Learnfinity
version: 1.0
active: true
count: 42
```

**Rules:**
- Use a colon `:` followed by a space
- No quotes needed for simple strings
- Use quotes for strings with special characters

### 2. Lists (Arrays)

```yaml
# List of items
fruits:
  - apple
  - banana
  - orange

# Alternative compact style
colors: [red, green, blue]
```

**Rules:**
- Use a dash `-` followed by a space
- Each item on a new line
- Indent consistently (usually 2 spaces)

### 3. Nested Objects

```yaml
# Nested structure
person:
  name: John Doe
  age: 30
  address:
    street: 123 Main St
    city: New York
    zipcode: 10001
```

**Rules:**
- Use consistent indentation (2 spaces recommended)
- Parent-child relationships shown through indentation

### 4. Comments

```yaml
# This is a comment
name: Learnfinity  # This is also a comment
# Comments are ignored by the parser
```

### 5. Multi-line Strings

```yaml
# Folded style (newlines become spaces)
description: >
  This is a very long description
  that spans multiple lines but
  will be treated as one line.

# Literal style (preserves newlines)
script: |
  echo "Line 1"
  echo "Line 2"
  echo "Line 3"
```

### 6. Boolean and Numbers

```yaml
# Booleans
enabled: true
disabled: false
debug: yes      # Also valid
production: no  # Also valid

# Numbers
port: 8080
temperature: 23.5
scientific: 1.2e+10
```

---

## ⚙️ How GitHub Actions Uses YAML

GitHub Actions uses YAML files to define **workflows** - automated processes that run when certain events occur in your repository.

### File Structure

```
your-repository/
├── .github/
│   └── workflows/           # Special directory for workflows
│       ├── ci.yml          # Your workflow files
│       ├── deploy.yml      # Each file = one workflow
│       └── test.yml        # Names can be anything.yml
```

### Basic Workflow Structure

Every GitHub Actions workflow follows this pattern:

```yaml
# 1. WORKFLOW NAME
name: My Awesome Workflow

# 2. WHEN TO RUN (Triggers)
on:
  push:
    branches: [master]
  pull_request:
    branches: [master]

# 3. WHAT TO RUN (Jobs)
jobs:
  my-job:                    # Job name
    runs-on: ubuntu-latest   # Operating system
    
    steps:                   # List of steps
      - name: Step 1
        run: echo "Hello World"
        
      - name: Step 2
        run: echo "This is step 2"
```

### Key Components Explained

#### 1. **`name`** - Workflow Title
```yaml
name: Learnfinity Plugin CI
```
This appears in the GitHub Actions tab.

#### 2. **`on`** - Trigger Events
```yaml
on:
  push:                      # When code is pushed
    branches: [master]       # Only on master branch
  pull_request:              # When PR is created
    branches: [master]       # Only PRs to master
  workflow_dispatch:         # Manual trigger button
```

#### 3. **`jobs`** - What to Execute
```yaml
jobs:
  test:                      # Job ID
    runs-on: ubuntu-22.04    # Virtual machine
    steps:                   # List of steps
      - name: Checkout code
        uses: actions/checkout@v4
```

#### 4. **`steps`** - Individual Actions
```yaml
steps:
  - name: Human-readable name
    uses: some-action@v1     # Use existing action
    with:                    # Parameters for action
      parameter: value
      
  - name: Run custom command
    run: |                   # Run shell commands
      echo "Custom command"
      php --version
```

---

## 📖 Understanding Your Workflow Files

Let's break down your actual workflow files to understand how they work!

### Your Plugin CI Workflow (`learnfinity-ci.yml`)

```yaml
name: Learnfinity Plugin CI
# 👆 This name appears in GitHub Actions tab

on: 
  push:
    branches: [master]        # Run when you push to master
  pull_request:
    branches: [master]        # Run when someone creates PR to master
  workflow_dispatch:          # Show "Run workflow" button in GitHub
    inputs:                   # Parameters you can enter manually
      plugin_path:
        description: 'Plugin path to test (e.g., local/myplugin). Leave empty to auto-detect.'
        required: false
        type: string
```

**Translation:** "Run this workflow when code is pushed to master, when PRs are created, or when manually triggered. Allow users to specify which plugin to test."

```yaml
jobs:
  test:                       # Job name: "test"
    runs-on: ubuntu-22.04     # Use Ubuntu 22.04 virtual machine
    
    env:                      # Environment variables
      PLUGIN_PATH: ${{ github.event.inputs.plugin_path || '' }}
```

**Translation:** "Create a job called 'test' that runs on Ubuntu. Set an environment variable for the plugin path."

```yaml
services:                     # Additional services (like databases)
  mysql:                      # Start a MySQL database
    image: mysql:8.4          # Use MySQL version 8.4
    env:                      # Database configuration
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: test
    ports:
      - 3306:3306             # Make database accessible on port 3306
```

**Translation:** "Start a MySQL database service that the tests can use."

```yaml
strategy:                     # Test matrix - run multiple combinations
  fail-fast: false           # Don't stop other tests if one fails
  matrix:                     # Define combinations to test
    include:
      - php: '8.3'            # Test with PHP 8.3
        moodle-branch: 'MOODLE_405_STABLE'  # and Moodle 4.05
        database: mysqli
        primary: true
        
      - php: '8.2'            # Also test with PHP 8.2
        moodle-branch: 'MOODLE_404_STABLE'  # and Moodle 4.04
        primary: false
```

**Translation:** "Run the tests multiple times with different PHP and Moodle versions. If one combination fails, keep testing the others."

```yaml
steps:                        # List of things to do
  - name: Check out repository code
    uses: actions/checkout@v4  # Download your code
    
  - name: Auto-detect Plugin Path
    run: |                     # Run custom shell commands
      echo "🔍 Auto-detecting plugins..."
      # ... shell script logic here
```

**Translation:** "First, download the code from the repository. Then run a script to find which plugins to test."

### Your Deployment Workflow (`deploy.yml`)

```yaml
name: Deploy Moodle (First Time) / Plugin Updates

on:
  workflow_run:               # Trigger after another workflow
    workflows: ["Learnfinity Plugin CI"]  # Wait for CI to complete
    types: [completed]        # When it finishes
    branches: [master]        # On master branch
  
  workflow_dispatch:          # Manual trigger button
  
  push:                       # Emergency trigger
    branches: [master]
    paths: ['.github/workflows/deploy.yml']  # Only if this file changes
```

**Translation:** "Run this deployment after the CI tests pass, when manually triggered, or when the deployment file itself is modified."

```yaml
jobs:
  deploy:
    runs-on: ubuntu-latest
    if: >                     # Complex condition
      github.event_name == 'workflow_dispatch' ||
      (github.event_name == 'push' && contains(github.event.head_commit.message, '.github/workflows/deploy.yml')) ||
      (github.event_name == 'workflow_run' && github.event.workflow_run.conclusion == 'success')
```

**Translation:** "Only run this job if it was manually triggered, or if it's an emergency push, or if the CI tests passed successfully."

```yaml
steps:
  - name: First-time Moodle Deployment
    if: steps.check-secrets.outputs.deployment_type == 'first_time'
    uses: appleboy/ssh-action@v1.0.3  # Use SSH action
    with:                      # Parameters for SSH action
      host: ${{ secrets.SERVER_HOST }}
      username: ${{ secrets.SERVER_USERNAME }}
      key: ${{ secrets.SERVER_SSH_KEY }}
      script: |               # Commands to run on server
        echo "🚀 Starting deployment..."
        cd /var/www/html
        git clone -b master https://github.com/user/repo.git moodle
```

**Translation:** "If this is a first-time deployment, connect to the server via SSH and run commands to install Moodle."

---

## 🎯 Common Patterns and Examples

### 1. Using Variables

```yaml
# Define variables
env:
  NODE_VERSION: '18'
  PHP_VERSION: '8.3'
  
steps:
  - name: Setup PHP
    uses: shivammathur/setup-php@v2
    with:
      php-version: ${{ env.PHP_VERSION }}  # Use the variable
```

### 2. Conditional Steps

```yaml
steps:
  - name: Run only on master
    if: github.ref == 'refs/heads/master'
    run: echo "This only runs on master branch"
    
  - name: Run only on success
    if: success()
    run: echo "Previous steps succeeded"
    
  - name: Run even on failure
    if: always()
    run: echo "This always runs"
```

### 3. Using Secrets

```yaml
steps:
  - name: Deploy to server
    env:
      SERVER_HOST: ${{ secrets.SERVER_HOST }}      # Secret value
      API_KEY: ${{ secrets.API_KEY }}              # Another secret
    run: |
      echo "Deploying to $SERVER_HOST"
      curl -H "Authorization: Bearer $API_KEY" https://api.example.com
```

### 4. Matrix Testing

```yaml
strategy:
  matrix:
    php: [8.1, 8.2, 8.3]      # Test all PHP versions
    os: [ubuntu-latest, windows-latest]  # On different OS
    
steps:
  - name: Setup PHP ${{ matrix.php }}
    uses: shivammathur/setup-php@v2
    with:
      php-version: ${{ matrix.php }}
```

### 5. File Uploads/Downloads

```yaml
steps:
  - name: Upload test results
    if: always()              # Even if tests fail
    uses: actions/upload-artifact@v4
    with:
      name: test-results
      path: test-results/
      retention-days: 7       # Keep for 7 days
```

---

## ✍️ Tips for Writing YAML

### 1. **Indentation is CRITICAL**

```yaml
# ✅ CORRECT
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - name: Step 1
        run: echo "Hello"

# ❌ WRONG - Inconsistent indentation
jobs:
  test:
  runs-on: ubuntu-latest
    steps:
    - name: Step 1
      run: echo "Hello"
```

### 2. **Use Consistent Spacing**

```yaml
# ✅ CORRECT - 2 spaces consistently
name: My Workflow
on:
  push:
    branches: [master]
  pull_request:
    branches: [master]

# ❌ WRONG - Mixed spacing
name: My Workflow
on:
    push:
        branches: [master]
  pull_request:
      branches: [master]
```

### 3. **Quote Special Characters**

```yaml
# ✅ CORRECT
message: "Don't forget to quote special characters!"
version: "2.0"              # Quote numbers that start with zero
time: "12:30"               # Quote time format

# ❌ WRONG
message: Don't forget quotes  # Apostrophe breaks parsing
version: 2.0.1-beta         # Hyphen might cause issues
```

### 4. **Use Multi-line for Complex Scripts**

```yaml
# ✅ CORRECT - Multi-line script
- name: Complex deployment
  run: |
    echo "Starting deployment..."
    if [ -f "config.php" ]; then
      echo "Config exists"
    else
      echo "Creating config"
      cp config-dist.php config.php
    fi
    echo "Deployment complete"

# ❌ HARDER TO READ - Single line
- name: Complex deployment  
  run: echo "Starting..." && if [ -f "config.php" ]; then echo "Config exists"; else echo "Creating config" && cp config-dist.php config.php; fi && echo "Complete"
```

### 5. **Use Comments Liberally**

```yaml
name: My Workflow

# Trigger on pushes to main branches
on:
  push:
    branches: [master, main]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
      # Download the repository code
      - name: Checkout
        uses: actions/checkout@v4
        
      # Install PHP with required extensions
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: mbstring, xml, curl  # Required for Moodle
```

---

## 🐛 Troubleshooting YAML Errors

### Common Error Messages and Solutions

#### 1. **"Invalid YAML: mapping values are not allowed here"**

```yaml
# ❌ WRONG - Missing space after colon
name:My Workflow
on:
  push:
    branches:[master]

# ✅ CORRECT - Space after colons
name: My Workflow
on:
  push:
    branches: [master]
```

#### 2. **"Invalid YAML: found character that cannot start any token"**

```yaml
# ❌ WRONG - Tab character used for indentation
jobs:
	test:           # This is a tab character
		runs-on: ubuntu-latest

# ✅ CORRECT - Use spaces only
jobs:
  test:             # These are spaces
    runs-on: ubuntu-latest
```

#### 3. **"Invalid YAML: could not find expected ':'"**

```yaml
# ❌ WRONG - Missing colon
steps
  - name: Test
    run: echo "Hello"

# ✅ CORRECT - Add colon
steps:
  - name: Test
    run: echo "Hello"
```

#### 4. **"Invalid YAML: block sequence expected"**

```yaml
# ❌ WRONG - List item not properly indented
steps:
- name: Test 1
  run: echo "Test 1"
- name: Test 2
  run: echo "Test 2"

# ✅ CORRECT - Proper indentation
steps:
  - name: Test 1
    run: echo "Test 1"
  - name: Test 2
    run: echo "Test 2"
```

### Debugging Tips

1. **Use a YAML Validator**
   - Online: https://www.yamllint.com/
   - VS Code Extension: "YAML" by Red Hat

2. **Check Indentation**
   - Use a text editor that shows spaces/tabs
   - Set editor to show whitespace characters

3. **Start Simple**
   - Begin with basic structure
   - Add complexity gradually
   - Test after each addition

4. **Use GitHub's Built-in Validation**
   - GitHub validates YAML when you commit
   - Check the Actions tab for syntax errors

---

## 🎓 Practice Exercise

Let's create a simple workflow together! Try to understand this example:

```yaml
# Name of the workflow
name: Simple Hello World

# When to run this workflow
on:
  push:
    branches: [master]    # Run when pushing to master
  workflow_dispatch:      # Allow manual triggering

# Jobs to run
jobs:
  hello:                  # Job name
    runs-on: ubuntu-latest # Operating system
    
    steps:                # Steps to execute
      - name: Say Hello   # Step name
        run: |            # Commands to run
          echo "Hello, World!"
          echo "Today is $(date)"
          echo "Current user: $(whoami)"
          
      - name: Show Environment
        run: |
          echo "GitHub Repository: ${{ github.repository }}"
          echo "GitHub Actor: ${{ github.actor }}"
          echo "GitHub Event: ${{ github.event_name }}"
```

**What this does:**
1. Creates a workflow called "Simple Hello World"
2. Runs when you push to master or trigger manually
3. Uses an Ubuntu virtual machine
4. Executes two steps that print information

---

## 🚀 Next Steps

Now that you understand YAML and GitHub Actions:

1. **Look at your existing workflows** with new understanding
2. **Try making small changes** to see how they work
3. **Read the GitHub Actions documentation** for more actions
4. **Experiment with simple workflows** before complex ones

### Useful Resources

- **GitHub Actions Documentation**: https://docs.github.com/en/actions
- **YAML Specification**: https://yaml.org/spec/1.2.2/
- **Actions Marketplace**: https://github.com/marketplace?type=actions
- **YAML Validator**: https://www.yamllint.com/

### Your Current Workflows

Now you can understand your workflows:
- **`learnfinity-ci.yml`**: Tests your plugins automatically
- **`deploy.yml`**: Deploys to your server when tests pass

Both use the concepts you just learned! 🎉

---

**Remember**: YAML is just a way to write structured configuration. GitHub Actions reads this structure and executes the steps you define. Start simple, and gradually build more complex workflows as you become comfortable with the syntax! 