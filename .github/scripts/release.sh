#!/usr/bin/env bash
# Release utilities of tool_sentry (used by .github/workflows/release.yml).
# Usage: bash .github/scripts/release.sh <command> [arguments]
#
#   meta              prints component, release, tag, folder, zip and prerelease (key=value)
#   check-tag <tag>   fails if the tag does not match $plugin->release in version.php
#   notes             checks the CHANGELOG.md section of the version and writes dist/notes.md
#   package [ref]     builds dist/<component>-<release>.zip with the plugin folder at the root (default: HEAD)
#   ci-green [sha]    waits for the CI jobs ("test (...)") of the commit and fails unless all of them succeeded
#   publish           creates the GitHub Release of the tag with the ZIP and the notes
#
# Variables: GITHUB_REPOSITORY (owner/repo) and GH_TOKEN (or an authenticated `gh`) for the API commands.
set -euo pipefail

fail() { echo "::error::$*" >&2; exit 1; }

read_version() {
  [ -f version.php ] || fail "version.php not found; run from the plugin root"
  component=$(awk -F"'" '/plugin->component/ {print $2; exit}' version.php)
  release=$(awk -F"'" '/plugin->release/ {print $2; exit}' version.php)
  maturity=$(grep -o 'MATURITY_[A-Z]*' version.php | head -1 || true)
  if [ -z "${component}" ] || [ -z "${release}" ] || [ -z "${maturity}" ]; then
    fail "Could not read component, release and maturity from version.php"
  fi
  folder="${component#*_}"
  zip="${component}-${release}.zip"
  tag="v${release}"
  if [ "${maturity}" = "MATURITY_STABLE" ]; then prerelease=false; else prerelease=true; fi
}

changes() {
  awk -v ver="$release" '
    $0 ~ "^## " ver "( |$)" { found = 1; next }
    found && /^## / { exit }
    found { print }
  ' CHANGELOG.md | sed -e :a -e '/^\n*$/{$d;N;ba' -e '}'
}

cmd="${1:-}"
[ -n "${cmd}" ] || fail "Missing command (meta, check-tag, notes, package, ci-green, publish)"
shift || true
read_version

case "${cmd}" in
  meta)
    printf '%s\n' "component=${component}" "release=${release}" "tag=${tag}" "folder=${folder}" "zip=${zip}" "prerelease=${prerelease}"
    ;;

  check-tag)
    want="${1:-}"
    [ -n "${want}" ] || fail "Usage: check-tag <tag>"
    [ "${want}" = "${tag}" ] || fail "Tag ${want} does not match \$plugin->release (${release}) in version.php (expected ${tag})"
    echo "Tag ${want} matches version.php"
    ;;

  notes)
    mkdir -p dist
    changes > dist/notes.md
    grep -q '[^[:space:]]' dist/notes.md || fail "CHANGELOG.md has no section (or an empty one) for version ${release}"
    {
      echo
      echo "### Installation"
      echo "Download \`${zip}\` (root folder \`${folder}\`) and install it at *Site administration > Plugins > Install plugins*, then run the Moodle upgrade."
    } >> dist/notes.md
    echo "Release notes written to dist/notes.md"
    ;;

  package)
    ref="${1:-HEAD}"
    mkdir -p dist
    git archive --format=zip --prefix="${folder}/" -o "dist/${zip}" "${ref}"
    unzip -Z1 "dist/${zip}" | grep -qx "${folder}/version.php" || fail "version.php is not inside ${folder}/ in the ZIP"
    ls -l "dist/${zip}"
    ;;

  ci-green)
    sha="${1:-$(git rev-parse HEAD)}"
    # The tag is usually pushed right after the branch: wait up to 40 minutes for the CI of the commit.
    for attempt in $(seq 1 80); do
      runs=$(gh api "repos/${GITHUB_REPOSITORY}/commits/${sha}/check-runs" --paginate \
        --jq '.check_runs[] | select(.name | startswith("test (")) | "\(.status) \(.conclusion)"')
      total=$(printf '%s\n' "${runs}" | grep -c . || true)
      pending=$(printf '%s\n' "${runs}" | grep -vc '^completed ' || true)
      ok=$(printf '%s\n' "${runs}" | grep -c '^completed success$' || true)
      if [ "${total}" -gt 0 ] && [ "${pending}" -eq 0 ]; then
        echo "CI jobs on ${sha:0:7}: ${ok}/${total} successful"
        [ "${ok}" -eq "${total}" ] || fail "CI is not green on ${sha:0:7}; fix it before releasing"
        exit 0
      fi
      echo "Waiting for the CI of ${sha:0:7} (${total} jobs, ${pending} pending)..."
      sleep 30
    done
    fail "CI of ${sha:0:7} did not finish in time"
    ;;

  publish)
    [ -f "dist/${zip}" ] && [ -f dist/notes.md ] || fail "Run notes and package first"
    flags=()
    if [ "${prerelease}" = "true" ]; then flags+=(--prerelease); fi
    gh release create "${tag}" "dist/${zip}" --repo "${GITHUB_REPOSITORY}" --verify-tag \
      --title "${component} ${release}" --notes-file dist/notes.md "${flags[@]}"
    ;;

  *)
    fail "Unknown command: ${cmd}"
    ;;
esac
