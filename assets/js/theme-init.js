(function () {
  try {
    var root = document.documentElement;
    var darkMode = localStorage.getItem('darkMode') === 'true';
    var fontSize = localStorage.getItem('fontSize') || 'medium';
    var themeColor = localStorage.getItem('themeColor') || 'blue';

    var colorMap = {
      blue: '#3b82f6',
      green: '#10b981',
      purple: '#8b5cf6',
      orange: '#f97316',
      cyan: '#06b6d4'
    };

    var sizeMap = {
      small: '14px',
      medium: '15px',
      large: '17px'
    };
    var scaleMap = {
      small: '0.933333',
      medium: '1',
      large: '1.133333'
    };

    var accent = colorMap[themeColor] || '#4f46e5';

    root.style.setProperty('--font-size-base', sizeMap[fontSize] || '15px');
    root.style.setProperty('--font-scale', scaleMap[fontSize] || '1');
    root.style.setProperty('--accent', accent);
    root.style.setProperty('--accent-hover', accent);

    var hex = accent.replace('#', '');
    if (hex.length === 6) {
      var r = parseInt(hex.substring(0, 2), 16);
      var g = parseInt(hex.substring(2, 4), 16);
      var b = parseInt(hex.substring(4, 6), 16);
      root.style.setProperty('--accent-soft', 'rgba(' + r + ', ' + g + ', ' + b + ', 0.14)');
    } else {
      root.style.setProperty('--accent-soft', 'rgba(79, 70, 229, 0.14)');
    }

    root.classList.toggle('dark-mode', darkMode);
  } catch (e) {
    // Fail silently: page remains on default theme.
  }
})();
