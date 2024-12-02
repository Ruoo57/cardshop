// webpack.config.js
const path = require('path');

module.exports = {
    mode: 'development',  // Ajoutez cette ligne pour spécifier le mode

    entry: './assets/app.js',  // Point d'entrée de votre application
    output: {
        path: path.resolve(__dirname, 'public/build'),
        filename: 'app.js',
    },
    resolve: {
        alias: {
            '@controllers': path.resolve(__dirname, 'assets/controllers'), // Pointage vers controllers
        }
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                    }
                }
            }
        ]
    },
    devServer: {
        contentBase: './public',
        hot: true,
    }
};
