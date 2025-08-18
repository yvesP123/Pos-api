const { spawn } = require('child_process');
const path = require('path');

module.exports = async (req, res) => {
  return new Promise((resolve, reject) => {
    // Set PHP environment variables
    const env = {
      ...process.env,
      REQUEST_METHOD: req.method,
      REQUEST_URI: req.url || '/',
      QUERY_STRING: req.url ? new URL(req.url, 'http://localhost').search.slice(1) : '',
      SCRIPT_FILENAME: path.join(process.cwd(), 'public/index.php'),
      DOCUMENT_ROOT: path.join(process.cwd(), 'public'),
      SERVER_NAME: req.headers.host || 'localhost',
      HTTP_HOST: req.headers.host || 'localhost',
      HTTPS: 'on'
    };

    // Add all HTTP headers
    Object.keys(req.headers).forEach(key => {
      env[`HTTP_${key.toUpperCase().replace('-', '_')}`] = req.headers[key];
    });

    const php = spawn('php-cgi', [path.join(process.cwd(), 'public/index.php')], {
      env: env,
      stdio: ['pipe', 'pipe', 'pipe']
    });

    let output = '';
    let error = '';

    php.stdout.on('data', (data) => {
      output += data.toString();
    });

    php.stderr.on('data', (data) => {
      error += data.toString();
    });

    php.on('close', (code) => {
      if (code !== 0) {
        res.status(500).json({ error: 'PHP execution failed', details: error });
        return resolve();
      }

      // Parse PHP-CGI output
      const [headers, body] = output.split('\r\n\r\n');
      
      if (headers) {
        headers.split('\r\n').forEach(header => {
          const [key, value] = header.split(': ');
          if (key && value) {
            res.setHeader(key, value);
          }
        });
      }

      res.end(body || output);
      resolve();
    });

    // Handle POST data
    if (req.method === 'POST' && req.body) {
      php.stdin.write(JSON.stringify(req.body));
    }
    php.stdin.end();
  });
};