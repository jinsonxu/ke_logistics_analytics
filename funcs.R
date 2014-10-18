# Institute of Systems Science #
# Master of Technology in Knowledge Engineering #
# Logistics Analytics #
# Assignment 3 functions file
# By: Jinson Xu
# Date: 18th October 2014


cleanPostalCode <- function(codeList) {
  # check if list is character class, convert if not
  if(class(codeList) != 'character') {
    codeList <- as.character(codeList)
  }
  
  ret <- sapply(codeList, function(x) {    
    x <- ifelse(nchar(x) < 6, paste('0',x,sep=''), x)
  })
} 