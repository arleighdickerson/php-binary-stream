var path = require('path');
var webpack = require('webpack');

var filename = 'ratchet-stream.js'
var plugins = []

if (process.env.NODE_ENV === 'production') {
  filename = 'ratchet-stream.min.js'
  plugins.push(
    new webpack.optimize.OccurrenceOrderPlugin(),
    new webpack.optimize.UglifyJsPlugin({
      compress: {
        screw_ie8: true,
        unused: true,
        dead_code: true,
        warnings: false,
        drop_console: true
      },
      mangle: {
        except: ['BinaryClient']
      },
      output: {
        comments: false
      }
    })
  )
}

module.exports = {
  entry: './js/entry.js',
  output: {
    path: path.resolve(__dirname, 'js', 'dist'),
    filename: filename,
  },
  plugins: plugins,
  module: {
    loaders: [
      {
        test: /\.js$/,
        loader: 'babel-loader',
        query: {
          presets: ['es2015']
        }
      }
    ]
  },
  stats: false
};