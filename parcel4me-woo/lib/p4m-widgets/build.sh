polymer build --entrypoint index.html -v --js-compile --js-minify --css-minify --html-minify --extra-dependencies p4m-profile/profile.png gfs-checkout-widget/images/GB/* p4m-shared/fonts/* p4m-shared/img/* p4m-card/assets/* gfs-droppoint/images/*
#polymer build --entrypoint index.html -v --extra-dependencies p4m-profile/profile.png gfs-checkout-widget/images/GB/* p4m-shared/fonts/* p4m-shared/img/* p4m-card/assets/* gfs-droppoint/images/*

mv build/default/bower_components/* build