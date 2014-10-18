# Institute of Systems Science 
# Master of Technology in Knowledge Engineering 
# Logistics Analytics 
# Assignment 3 
# By: Jinson Xu
# Date: 18th October 2014

# clear workspace
rm(list=ls()) 

library(jsonlite)
library(httr)

# load custom functions
source('funcs.R')
source('config.R')


# read in our data files
plainRoutingInfo <- read.csv('data/1_vrp_8_plain_routing.csv')
fleetInfo <- read.csv('data/fleet.csv')

plainRoutingInfo[1] <- cleanPostalCode(plainRoutingInfo[,c(1)])  # clean our network 'id' first
depot <- plainRoutingInfo[1,1]  # assumption: First postal code is the depot.
#shiftStart <- '8:00';
#shiftEnd <- '17:30';


# create network list
names(plainRoutingInfo) <- c('name', 'lat', 'lng')
plainRoutingList <- split(plainRoutingInfo, plainRoutingInfo$name)

# create fleet list
names(fleetInfo) <- c('vehicle', 'capacity', 'shift-start', 'shift-end')
fleetInfo['start-location'] <- depot  # assumption: all trucks start and end at depot
fleetInfo['end-location'] <- depot  # assumption: all trucks start and end at depot
fleetInfoList <- split(fleetInfo, fleetInfo$vehicle)

# create visits list
visitsInfo <- plainRoutingInfo
visitsInfo$duration <- 5
visitsInfo <- visitsInfo[,c(1,4)]
visitsInfoList <- split(visitsInfo, visitsInfo$name)


# generate JSON object
networkJSON <- toJSON(list(network=plainRoutingList, 
                           fleet=fleetInfoList,
                           visits=visitsInfoList))
networkJSON <- gsub('\\[|\\]','',networkJSON, perl=TRUE)
writeLines(networkJSON,'q1_json.txt')  # take a look

postBody <- list(network=plainRoutingList, 
             fleet=fleetInfoList, 
             visits=visitsInfoList)

# send request to routific
r <- POST("https://routific.com/api/vrp-long", 
          add_headers(Authorization = token),  # the token variable is set in config.R which is not uploaded to GitHub for obvious reasons :P
          body = postBody,
          encode=c('json'))
#str(content(r))
content(r, "parsed")