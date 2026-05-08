#!/bin/bash

# Howard Permissions - Apply Permissions Script
# Usage: ./apply-permissions.sh

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$SCRIPT_DIR/../../../.."

# Site URI for Drush multisite routing. Override with an env variable:
# DRUSH_URI=https://mysite.lndo.site ./apply-permissions.sh
DRUSH_URI_ARG="${DRUSH_URI:+--uri=$DRUSH_URI}"

echo "Howard University Permissions Application"
echo "========================================"

# Function to apply permissions
apply_permissions() {
    echo "Applying Howard University role permissions..."
    cd "$PROJECT_ROOT"
    
    # Run the permission application function
    RESULT=$(lando drush eval "
        \$result = howard_permissions_apply_all_permissions();
        if (\$result['success']) {
            echo 'SUCCESS: ' . \$result['message'] . \"\\n\";
            foreach (\$result['details'] as \$detail) {
                echo '  - ' . \$detail . \"\\n\";
            }
        } else {
            echo 'ERROR: ' . \$result['message'] . \"\\n\";
            exit(1);
        }
    " $DRUSH_URI_ARG)
    
    echo "$RESULT"
}

# Function to check permissions status
check_status() {
    echo "Checking Howard University permissions status..."
    cd "$PROJECT_ROOT"
    
    RESULT=$(lando drush eval "
        \$permissions = howard_permissions_get_enforced_permissions();
        if (empty(\$permissions)) {
            echo 'ERROR: No permissions loaded from JSON file\\n';
            exit(1);
        } else {
            echo 'SUCCESS: ' . count(\$permissions) . ' permissions loaded from JSON\\n';
            echo 'Core roles: anonymous, authenticated, administrator, site_admin, site_builder\\n';
        }
    " $DRUSH_URI_ARG)
    
    echo "$RESULT"
}

# Function to validate JSON
validate_json() {
    echo "Validating JSON configuration..."
    cd "$SCRIPT_DIR"
    
    if [ ! -f "permissions_roles.json" ]; then
        echo "ERROR: permissions_roles.json file not found!"
        exit 1
    fi
    
    # Use PHP to validate JSON
    cd "$PROJECT_ROOT"
    lando php -r "
        \$json_file = 'docroot/modules/custom/howard_permissions/permissions_roles.json';
        if (!file_exists(\$json_file)) {
            echo 'ERROR: JSON file not found at ' . \$json_file . \"\\n\";
            exit(1);
        }
        
        \$json_content = file_get_contents(\$json_file);
        \$data = json_decode(\$json_content, TRUE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'ERROR: Invalid JSON - ' . json_last_error_msg() . \"\\n\";
            exit(1);
        }
        
        if (!isset(\$data['permissions']) || !is_array(\$data['permissions'])) {
            echo 'ERROR: JSON does not have valid permissions structure\\n';
            exit(1);
        }
        
        echo 'SUCCESS: JSON is valid with ' . count(\$data['permissions']) . ' permissions\\n';
    "
}

# Function to show help
show_help() {
    cat << EOF
Howard Permissions - Apply Permissions Script

Usage: $0 [COMMAND]

Commands:
    apply       Apply all permissions from JSON (default)
    status      Check current permissions status
    validate    Validate JSON configuration
    help        Show this help message

Examples:
    $0 apply            # Apply permissions
    $0 status           # Check status
    $0 validate         # Validate JSON
    
This script is equivalent to running:
    drush eval "howard_permissions_apply_all_permissions();"
    
But provides better error handling and output formatting.

EOF
}

# Main script logic
case "${1:-apply}" in
    "apply")
        validate_json
        apply_permissions
        ;;
    "status")
        validate_json  
        check_status
        ;;
    "validate")
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
echo "Complete!"