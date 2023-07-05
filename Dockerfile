ARG MW_VERSION
ARG PHP_VERSION

FROM gesinn/mediawiki-ci:${MW_VERSION}-php${PHP_VERSION}

RUN rm -f /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini && \
    sed -i s/80/8080/g /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf


ENV EXTENSION=SemanticExtraSpecialProperties
COPY composer*.json /var/www/html/extensions/$EXTENSION/

ARG SMW_VERSION
ARG AR_VERSION
RUN curl -L https://github.com/wikimedia/mediawiki-extensions-ApprovedRevs/tarball/${AR_VERSION} \
    | tar xz --strip-components 1 --one-top-level=extensions/ApprovedRevs && \
    COMPOSER=composer.local.json composer require --no-update mediawiki/semantic-media-wiki ${SMW_VERSION} && \
    COMPOSER=composer.local.json composer require --no-update mediawiki/semantic-extra-special-properties @dev && \
    COMPOSER=composer.local.json composer config repositories.semantic-extra-special-properties \
        '{"type": "path", "url": "extensions/${EXTENSION}"}' && \
    composer update

COPY . /var/www/html/extensions/$EXTENSION

# Create file containing PHP code to setup extension; to be appended to LocalSettings.php
RUN echo \
        '$wgDBerrorLog = "/data/log/dberror.log";\n' \
        '$wgDebugLogGroups["exception"] = "/data/log/exception.log";\n' \
        'if (file_exists( "$IP/LocalSettings.Pre.php" )) require_once( "$IP/LocalSettings.Pre.php" );\n' \
        '$smwgConfigFileDir = "/data/config";\n' \
        'wfLoadExtension( "SemanticMediaWiki" );\n' \
        'enableSemantics( $wgServer );\n' \
        'wfLoadExtension( "ApprovedRevs" );\n' \
        '$egApprovedRevsAutomaticApprovals = false;\n' \
        "wfLoadExtension( '$EXTENSION' );\n" \
        '$sespgEnabledPropertyList[] = "_CUSER";\n' \
        '$sespgEnabledPropertyList[] = "_REVID";\n' \
        '$sespgEnabledPropertyList[] = "_SUBP";\n' \
        '$sespgEnabledPropertyList[] = "_APPROVED";\n' \
        '$sespgEnabledPropertyList[] = "_APPROVEDBY";\n' \
        '$sespgEnabledPropertyList[] = "_APPROVEDDATE";\n' \
        '$sespgEnabledPropertyList[] = "_APPROVEDSTATUS";\n' \
        'if (file_exists( "$IP/LocalSettings.Post.php" )) require_once( "$IP/LocalSettings.Post.php" );\n' \
    >> __setup_extension__
