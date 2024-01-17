package booxtract

import (
	"fmt"
	"github.com/spf13/cobra"
	"os"
)

var rootCmd = &cobra.Command{
	Use:   "booxtract",
	Short: "booxtract",
	Long:  `booxtract`,
	Run: func(cmd *cobra.Command, args []string) {
		// implement
	},
}

func Execute() {
	if err := rootCmd.Execute(); err != nil {
		_, err = fmt.Fprintf(os.Stderr, "Error while executing booxtract '%s'", err)

		if err != nil {
			return
		}

		os.Exit(1)
	}
}
