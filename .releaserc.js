module.exports = {
  tagFormat: "${version}",
  branch: "master",
  plugins: [
    "@semantic-release/commit-analyzer",
    "@semantic-release/release-notes-generator",
    [
      "@semantic-release/changelog",
      {
        "changelogFile": "changelog.txt"
      }
    ],
    [
      "@semantic-release/git",
      {
        "assets": ["CHANGELOG.md"]
      }
    ],
    ["@semantic-release/npm", { npmPublish: false }],
    "@semantic-release/github",
    [
      "semantic-release-plugin-update-version-in-files",
      {
        "files": [
          "wp-zoom.php",
          "readme.txt"
        ],
        "placeholder": "0.0.0-development"
      }
    ]
  ]
};
