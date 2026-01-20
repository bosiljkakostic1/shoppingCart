# React DevTools Setup Guide

## Browser Extension Installation

React DevTools is a browser extension that allows you to inspect React component hierarchies, props, state, and more.

### Chrome/Edge (Chromium-based)
1. Visit the [Chrome Web Store](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)
2. Click "Add to Chrome"
3. The extension will be installed automatically

### Firefox
1. Visit the [Firefox Add-ons](https://addons.mozilla.org/en-US/firefox/addon/react-devtools/)
2. Click "Add to Firefox"
3. Restart Firefox if prompted

### Standalone (for React Native or other environments)
```bash
npm install -g react-devtools
react-devtools
```

## Usage

1. **Open your application** in the browser (e.g., `http://localhost:8000`)
2. **Open Developer Tools** (F12 or Right-click → Inspect)
3. **Look for "⚛️ Components" tab** in the DevTools panel
4. **Select components** in the tree to inspect:
   - Component props
   - Component state
   - Component hooks
   - Component hierarchy

## Features

- **Component Tree**: Visual representation of your React component hierarchy
- **Props Inspector**: View and edit component props in real-time
- **State Inspector**: View component state and hooks
- **Profiler**: Performance profiling for React components
- **Search**: Find components by name

## Configuration

This project is already configured to work with React DevTools. The app runs in development mode when using `npm run dev`, which enables:

- Full component names (not minified)
- Source maps for debugging
- React DevTools integration

## Troubleshooting

### DevTools not showing React components
1. Ensure you're running in development mode (`npm run dev`)
2. Refresh the page
3. Check that React DevTools extension is enabled in your browser
4. Try disabling and re-enabling the extension

### Components not appearing
- Make sure you're on a page that uses React (not a static HTML page)
- Check the browser console for any errors
- Verify React is loaded correctly

## Additional Resources

- [React DevTools Documentation](https://react.dev/learn/react-developer-tools)
- [Chrome Extension](https://chrome.google.com/webstore/detail/react-developer-tools/fmkadmapgofadopljbjfkapdkoienihi)
- [Firefox Add-on](https://addons.mozilla.org/en-US/firefox/addon/react-devtools/)
