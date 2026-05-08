#!/bin/bash

# Howard Permissions Test Runner
# Usage: ./run-tests.sh [unit|functional|all]

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$SCRIPT_DIR/../../../.."

echo "Howard Permissions Module - Test Runner"
echo "======================================="

# Function to run unit tests
run_unit_tests() {
    echo "Running Unit Tests..."
    cd "$PROJECT_ROOT"
    lando php vendor/bin/phpunit \
        docroot/modules/custom/howard_permissions/tests/src/Unit/ \
        --bootstrap docroot/core/tests/bootstrap.php \
        --testdox
}

# Function to run functional tests  
run_functional_tests() {
    echo "Running Functional Tests..."
    cd "$PROJECT_ROOT"
    lando php vendor/bin/phpunit \
        docroot/modules/custom/howard_permissions/tests/src/Functional/ \
        --bootstrap docroot/core/tests/bootstrap.php \
        --testdox
}

# Function to run all tests
run_all_tests() {
    echo "Running All Tests..."
    cd "$PROJECT_ROOT"
    lando php vendor/bin/phpunit \
        docroot/modules/custom/howard_permissions/tests/ \
        --bootstrap docroot/core/tests/bootstrap.php \
        --testdox
}

# Function to validate module files
validate_syntax() {
    echo "Validating PHP syntax..."
    
    files=(
        "howard_permissions.module"
        "howard_permissions.install"
        "tests/src/Unit/HowardPermissionsTest.php"
        "tests/src/Functional/HowardPermissionsFunctionalTest.php"
    )
    
    for file in "${files[@]}"; do
        if [ -f "$SCRIPT_DIR/$file" ]; then
            echo "Checking $file..."
            cd "$PROJECT_ROOT"
            lando php -l "docroot/modules/custom/howard_permissions/$file"
        else
            echo "Warning: $file not found"
        fi
    done
}

# Function to validate JSON
validate_json() {
    echo "Validating JSON configuration..."
    cd "$SCRIPT_DIR"
    if [ -f "permissions_roles.json" ]; then
        lando php -r "
            \$json = file_get_contents('permissions_roles.json');
            \$data = json_decode(\$json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo 'JSON is valid\n';
                if (isset(\$data['permissions']) && is_array(\$data['permissions'])) {
                    echo 'Found ' . count(\$data['permissions']) . ' permissions in JSON\n';
                } else {
                    echo 'Warning: JSON does not have valid permissions structure\n';
                }
            } else {
                echo 'JSON Error: ' . json_last_error_msg() . '\n';
                exit(1);
            }
        "
    else
        echo "Warning: permissions_roles.json not found"
    fi
}

# Function to show help
show_help() {
    cat << EOF
Howard Permissions Test Runner

Usage: $0 [COMMAND]

Commands:
    unit        Run unit tests only
    functional  Run functional tests only
    all         Run all tests (default)
    validate    Validate PHP syntax and JSON
    help        Show this help message

Examples:
    $0 unit                 # Run unit tests
    $0 functional          # Run functional tests  
    $0 all                 # Run all tests
    $0 validate            # Validate files
    
EOF
}

# Main script logic
case "${1:-all}" in
    "unit")
        validate_syntax
        validate_json
        run_unit_tests
        ;;
    "functional")
        validate_syntax
        validate_json
        run_functional_tests
        ;;
    "all")
        validate_syntax
        validate_json
        run_all_tests
        ;;
    "validate")
        validate_syntax
        validate_json
        ;;
    "help"|"-h"|"--help")
        show_help
        ;;
    *)
        echo "Unknown command: $1"
        show_help
        exit 1
        ;;
esac

echo ""
echo "Test run complete!"