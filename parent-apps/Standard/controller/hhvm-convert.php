<?php

if(ENVIRONMENT != "local") { exit; }

Classes_Convert::massConversion(true, true, true);