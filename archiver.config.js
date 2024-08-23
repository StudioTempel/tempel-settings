import fs from "fs";
import archiver from "archiver";

const inputGlobArray = [
  "**/*.php",
  "admin/**/*",
  "helpers/**/*",
  "includes/**/*",
  "vendor/**/*",
  "public/**/*",
  "dist/**/*",
  "languages/**/*",
  "*.md",
  "LICENSE",
];

const version = "2.0.9";
const filename = `tempel-settings-${version}.zip`;

const output = fs.createWriteStream("build/" + filename);
const archive = archiver("zip", {
  zlib: { level: 9 },
});

output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log(
    "archiver has been finalized and the output file descriptor has closed.",
  );
});

output.on("end", function () {
  console.log("Data has been drained");
});

archive.on("warning", function (err) {
  if (err.code === "ENOENT") {
    console.log(err);
  } else {
    throw err;
  }
});

archive.on("error", function (err) {
  throw err;
});

archive.pipe(output);

archive.directory("admin/", "admin");
archive.directory("dist/", "dist");
archive.directory("helpers/", "helpers");
archive.directory("includes/", "includes");
archive.directory("vendor/", "vendor");
archive.directory("languages/", "languages");
archive.directory("public/", "public");

archive.file("tempel.php", { name: "tempel.php" });
// archive.file("LICENSE.md", { name: "LICENSE" });
archive.file("README.md", { name: "README.md" });

archive.finalize();
