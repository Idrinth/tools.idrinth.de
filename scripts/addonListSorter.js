var AddonListSorter = function() {
    var publicAPI = {
        sortBy: sortBy,
        init: init
    };
    
    var _results = [];
    var _container = document.getElementsByClassName("searchResults")[0]; //TODO: This is iffy but will do for now, DON'T CHECK IT IN #################

    var _sortFunctions = {
        alphabeticalName: (a, b) => a.data.name <= b.data.name ? -1 : 1,
        alphabeticalNameReverse: (a, b) => a.data.name <= b.data.name ? 1 : -1,
        highToLowDownloads: (a, b) => b.data.downloads - a.data.downloads,
        lowToHighDownloads: (a, b) => a.data.downloads - b.data.downloads,
        highToLowEndorsements: (a, b) => b.data.endorsements - a.data.endorsements,
        lowToHighEndorsements: (a, b) => a.data.endorsements - b.data.endorsements,
        mostToLeastRecentUpdate: (a, b) => b.data.lastUpdate - a.data.lastUpdate,
        leastToMostRecentUpdate: (a, b) => a.data.lastUpdate - b.data.lastUpdate,
    }

    function _buildDataStructureFromSearchResults() {

        var searchResultElements = document.getElementsByClassName("addonListing");
        for(var i = 0; i < searchResultElements.length; i++) {
            _results.push( {
                "element": searchResultElements[i],
                "data": JSON.parse(searchResultElements[i].dataset.addon)
            });
        }


    }

    function _updateOrder() {
        for(var i = _results.length - 1; i >= 0; i--) {
            if(_container.children.length >  1) {
                _container.insertBefore(_results[i].element, _container.children[1]);
            }
        }
    }

    function sortBy(sortFunctionName) {
        _results.sort(_sortFunctions[sortFunctionName]);
        _updateOrder();
    }

    function init() {
        _buildDataStructureFromSearchResults();
        var dropDown = document.getElementById("sortBy");
        dropDown.addEventListener("change", () =>  sortBy(dropDown.value));
    }

    return publicAPI;
}();

document.addEventListener("DOMContentLoaded", function(event) { 
    AddonListSorter.init();
});