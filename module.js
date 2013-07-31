if (!M.block_bayes) {
	M.block_bayes = {
		/**
		 * @memberOf M.block_bayes
		 */
		quiz_results_init: function(Y) {
			Y.on("click", function(e) {
				var usageid = e.target.getData("usageid");
				var node = Y.one("#debug_"+usageid);
				node.setStyle("display", node.getStyle("display") == "none" ? "block" : "none");
			}, ".debug-toggle");
		}
	};
}