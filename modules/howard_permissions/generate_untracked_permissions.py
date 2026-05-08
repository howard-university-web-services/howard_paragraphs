import json

# Load all system permissions
with open('/tmp/all_permissions.json', 'r') as f:
    all_system_perms = set(json.load(f))

# Load tracked permissions  
with open('permissions_roles.json', 'r') as f:
    tracked_data = json.load(f)
    tracked_perms = {perm['permission'] for perm in tracked_data['permissions']}

untracked_perms = sorted(all_system_perms - tracked_perms)

# Create untracked permissions data structure
untracked_data = {
    "untracked_permissions": []
}

for perm in untracked_perms:
    # Determine provider and title from permission name
    provider = "unknown"
    title = perm.replace("_", " ").title()
    
    # Map to providers based on permission patterns
    if "admin_toolbar" in perm:
        provider = "admin_toolbar"
    elif "antibot" in perm:
        provider = "antibot"  
    elif "block" in perm:
        provider = "block"
    elif "captcha" in perm or "CAPTCHA" in perm:
        provider = "captcha"
    elif "config" in perm:
        provider = "config"
    elif "editorial" in perm or "workflow" in perm:
        provider = "workflows"
    elif "content_moderation" in perm:
        provider = "content_moderation"
    elif "contextual" in perm:
        provider = "contextual"
    elif "crop" in perm:
        provider = "crop"
    elif "devel" in perm:
        provider = "devel"
    elif "embed" in perm:
        provider = "embed"
    elif "entity_browser" in perm:
        provider = "entity_browser"
    elif "entity_clone" in perm or "clone" in perm:
        provider = "entity_clone"
    elif "field" in perm:
        provider = "field"
    elif "file" in perm:
        provider = "file"
    elif "filter" in perm or "format" in perm:
        provider = "filter"
    elif "help" in perm:
        provider = "help"
    elif "honeypot" in perm:
        provider = "honeypot"
    elif "image" in perm:
        provider = "image"
    elif "language" in perm:
        provider = "language"
    elif "layout" in perm:
        provider = "layout_builder"
    elif "link" in perm:
        provider = "link"
    elif "media" in perm:
        provider = "media"
    elif "memcache" in perm:
        provider = "memcache"
    elif "menu" in perm:
        provider = "menu_ui"
    elif "metatag" in perm or "meta" in perm:
        provider = "metatag"
    elif "module" in perm:
        provider = "module_filter"
    elif "node" in perm:
        provider = "node"
    elif "openid" in perm:
        provider = "openid_connect"
    elif "paragraph" in perm:
        provider = "paragraphs"
    elif "password" in perm:
        provider = "password_policy"
    elif "path" in perm:
        provider = "path"
    elif "pathauto" in perm:
        provider = "pathauto"
    elif "quickedit" in perm or "place editing" in perm:
        provider = "quickedit"
    elif "rdf" in perm:
        provider = "rdf"
    elif "search" in perm:
        provider = "search"
    elif "shield" in perm:
        provider = "shield"
    elif "shortcut" in perm:
        provider = "shortcut"
    elif "sitemap" in perm:
        provider = "simple_sitemap"
    elif "system" in perm or "administration" in perm or "maintenance" in perm or "reports" in perm or "software" in perm or "theme" in perm:
        provider = "system"
    elif "taxonomy" in perm:
        provider = "taxonomy"
    elif "toolbar" in perm:
        provider = "toolbar"
    elif "tour" in perm:
        provider = "tour"
    elif "user" in perm:
        provider = "user"
    elif "video" in perm:
        provider = "video_embed_field"
    elif "view" in perm:
        provider = "views"
    elif "webform" in perm:
        provider = "webform"
    
    untracked_data["untracked_permissions"].append({
        "permission": perm,
        "title": title,
        "provider": provider,
        "roles": ["unassigned"]
    })

# Save to file
with open('untracked_permissions.json', 'w') as f:
    json.dump(untracked_data, f, indent=2)

print(f"Generated untracked_permissions.json with {len(untracked_perms)} permissions")