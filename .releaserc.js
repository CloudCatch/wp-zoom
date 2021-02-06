module.exports = {
  tagFormat: "${version}",
  branch: "master",
  plugins: [
    ["@semantic-release/npm", { npmPublish: false }],
    "@semantic-release/github",
  ],
  prepare: [
    [
      "semantic-release-version-bump",
      {
        files: "wp-zoom.php",
      },
    ],
  ],
};
