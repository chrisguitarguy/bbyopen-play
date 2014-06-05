# BBYOPEN Playground

This was for a little coding test and uses the Best Buy API.

## Running the Site Locally

You'll need an [API key](https://remix.mashery.com/member/register) to run the
site.

    shell$ git clone https://github.com/chrisguitarguy/bbyopen-play.git
    shell$ cd bbyopen-play
    shell$ wget https://getcomposer.org/composer.phar
    shell$ php composer.phar install
    shell$ BBY_APIKEY=YOURAPIKEY php -t web/ -S 127.0.0.1:8080
