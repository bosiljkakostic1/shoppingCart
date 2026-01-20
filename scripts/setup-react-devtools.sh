#!/bin/bash

# React DevTools Setup Script
# This script helps you install React DevTools browser extension

echo "🔧 React DevTools Setup"
echo "======================"
echo ""

# Detect browser
if command -v google-chrome &> /dev/null || command -v chromium-browser &> /dev/null; then
    echo "✅ Chrome/Chromium detected"
    echo ""
    echo "To install React DevTools:"
    echo "1. Open Chrome and go to:"
    echo "   https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi"
    echo "2. Click 'Add to Chrome'"
    echo "3. Restart Chrome if prompted"
    echo ""
elif command -v firefox &> /dev/null; then
    echo "✅ Firefox detected"
    echo ""
    echo "To install React DevTools:"
    echo "1. Open Firefox and go to:"
    echo "   https://addons.mozilla.org/en-US/firefox/addon/react-devtools/"
    echo "2. Click 'Add to Firefox'"
    echo "3. Restart Firefox if prompted"
    echo ""
else
    echo "⚠️  No supported browser detected"
    echo ""
    echo "Please install React DevTools manually:"
    echo "- Chrome: https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi"
    echo "- Firefox: https://addons.mozilla.org/en-US/firefox/addon/react-devtools/"
    echo ""
fi

echo "📖 Usage Instructions:"
echo "1. Start your development server: npm run dev"
echo "2. Open your app in the browser (http://localhost:8000)"
echo "3. Open Developer Tools (F12)"
echo "4. Look for the '⚛️ Components' tab"
echo ""
echo "For more information, see REACT_DEVTOOLS.md"
