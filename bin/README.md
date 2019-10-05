One task I can not do in pure PHP is getting the window size of the terminal window, so on \*nix systems, I call `echo "$(tput cols);$(tput lines)"`, but Windows has no such commodities, so I've crafted `get_window_size.exe` to output exactly the same there, and along with it, I put its source code into this folder.